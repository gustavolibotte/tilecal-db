<?php

namespace App\Controllers;

use App\Library\Forms\EquipmentBatchRegisterForm;
use App\Validation\Validators\BatchRegisterValidator;
use App\Validation\Validators\EquipmentAssembleValidator;
use App\Validation\Validators\EquipmentValidator;
use CodeIgniter\HTTP\ResponseInterface;
use ValueError;
use Throwable;

class Equipments extends APIController
{
    public function assemble(string $equipmentId)
    {
        $feature = model('FeatureModel')->findName('parent_relation');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }
        $validator = (new EquipmentAssembleValidator())->addRequest($this->request);
        $messenger = \Config\Services::messenger();
        if ($validator->validate()){
            $validatedData = $validator->getValidatedData();
            model('EquipmentModel')->updateParent(
                $validatedData['child_id'],
                $validatedData['parent_id'],
                $validatedData['slot_id']
            );
            $messenger->addMessage(
                sprintf('Assembled equipment %s to %s',
                    $validatedData['child_id'],
                    $validatedData['parent_id']
                ),
                'success'
            );
        } else{
            foreach ($validator->getErrors() as $errorMsg){
                $messenger->addMessage($errorMsg, 'danger');
            }
        }
        $messenger->dumpMessages();
        return redirect()->route('equipment_view', [$equipmentId]);
    }

    public function get(): ResponseInterface
    {
        try{
            return $this->__get__();
        } catch (Throwable $e){
            return $this->throwError(500, $e);
        }
    }

    protected function __get__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('api_equipment_get');
        if (!$feature->isActive()){
            return $this->fail("", 503);
        }

        $equipmentModel = model('EquipmentModel')->asArray();
        $equipmentModel = $this->parseFilterCondition('id', 'equipments.id', $equipmentModel);
        $equipmentModel = $this->parseFilterCondition('type_name', 'equipment_types.name', $equipmentModel);

        if (!empty($this->request->getVar('relatedFileId'))){
            $equipmentModel = $this->parseFilterCondition(
                'relatedFileId',
                'equipments_to_files.file_id',
                $equipmentModel->joinFiles()
            );
        }

        $deleteMsg = $this->checkDeletion();
        if (!empty($this->request->getVar('onlyDeleted'))){
            if (!empty($deleteMsg)){
                return $this->failForbidden(message: $deleteMsg);
            }
            $equipmentModel = $equipmentModel->onlyDeleted();
        } elseif (!empty($this->request->getVar('withDeleted'))){
            if (!empty($deleteMsg)){
                return $this->failForbidden(message: $deleteMsg);
            }
            $equipmentModel = $equipmentModel->withDeleted();
        }

        $this->parseOrdering('orderBy', $equipmentModel->getTable(), $equipmentModel);
        $this->parseReturnType('returnType', $equipmentModel);

        $relatedEquipmentId = ($this->request->getVar('relatedEquipmentId'));
        if (empty($relatedEquipmentId)){
            $data = $equipmentModel->findAll();
        } else {
            $data = $equipmentModel->getRelatedEquipments($relatedEquipmentId);
        }
        helper('app_array');
        foreach($data as &$sample){
            $sample['type_params'] = json_decode($sample['type_params']);
        }
        return $this->buildAPIResponse(200, empty($data)? []: $data);
    }

    public function post(): ResponseInterface
    {
        try{
            return $this->__post__();
        } catch (Throwable $e){
            return $this->throwError(500, $e);
        }
    }

    protected function __post__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_register');
        if (!$feature->isActive()){
            return $this->fail('Service Unavailable', 503);
        }

        if (!$this->request->is('post')){
            return $this->fail('Equipments::post just accepts post requests', 400);
        }
        $data = ['type_name' => $this->request->getVar('type_name')];
        $rules = [
            'type_name' => [
                'rules' => [
                    'required',
                    'is_not_unique[equipment_types.name]'
                ],
                'errors' => [
                    'required' => 'Equipment type name is required.',
                    'is_not_unique' => 'The equipment type "{value}" doesn\'t exist.'
                ]
            ]
        ];

        if (!$this->validateData($data, $rules)){
            $errors = $this->validator->getErrors();
            return $this->fail([], 400, $errors['type_name']);
        }

        $validator = EquipmentValidator::getFromTypeName($data['type_name']);
        $validator->addRequest($this->request);
        if ($validator->validate()){
            $equipment = $validator->getValidatedData();
            $equipmentModel = model('EquipmentModel');
            $equipmentModel->insert($equipment);
            return $this->buildAPIResponse(200, [], 'Registered with success.');
        }else{
            $errors = $validator->getErrors();
            return $this->fail($errors, 400, 'Invalid data.');
        }
    }

    public function getHist(): ResponseInterface
    {
        try{
            return $this->__getHist__();
        } catch (Throwable $e){
            return $this->throwError(500, $e);
        }
    }

    protected function __getHist__(): ResponseInterface
    {
        $id = $this->request->getVar('id');
        if (empty($id)){
            throw new ValueError('An id value must be given.');
        }
        $history = model('HistModel')
            ->getEquipmentHist($id);
        return $this->buildAPIResponse(200, $history);
    }

    public function put(): ResponseInterface
    {
        try{
            return $this->__put__();
        } catch (Throwable $e){
            return $this->throwError(500, $e);
        }
    }

    protected function __put__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_edit');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }
        $data = ['id' => $this->request->getVar('id')];
        $rules = [
            'id' => [
                'rules' => [
                    'required',
                    'is_not_unique[equipments.id]'
                ],
                'errors' => [
                    'required' => 'Equipment ID is required.',
                    'is_not_unique' => 'Equipment with ID "{value}" doesn\'t exist.'
                ]
            ]
        ];
        if (!$this->validateData($data, $rules)){
            $errors = $this->validator->getErrors();
            return $this->fail([], 400, $errors['id']);
        }
        $equipmentModel = model('EquipmentModel');
        $equipment = $equipmentModel
            ->asArray()
            ->fullQuery()
            ->find($data['id']);
        $validator = EquipmentValidator::getFromTypeName($equipment['type_name'])
            ->addRequest($this->request)
            ->asEdit();
        if ($validator->validate()){
            $updatedEquipment = $validator->getValidatedData();
            $equipmentModel->update($data['id'], $updatedEquipment);
            return $this->buildAPIResponse(200, [], 'Altered equipment with success.');
        }
        $errors = $validator->getErrors();
        return $this->fail($errors, 400, 'Invalid data.');
    }

    public function delete(): ResponseInterface
    {
        try{
            return $this->__delete__();
        } catch (Throwable $e){
            return $this->throwError(500, $e);
        }
    }

    protected function __delete__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_delete');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

        $data = ['id' => $this->request->getVar('id')];
        $rules = [
            'id' => [
                'rules' => [
                    'required',
                    'is_not_unique[equipments.id]'
                ],
                'errors' => [
                    'required' => 'Equipment ID is required.',
                    'is_not_unique' => 'Equipment with ID "{value}" doesn\'t exist.'
                ]
            ]
        ];

        if (!$this->validateData($data, $rules)){
            $errors = $this->validator->getErrors();
            return $this->fail([], 400, $errors['id']);
        }
        model('EquipmentModel')->delete($data['id']);
        return $this->buildAPIResponse(200, [], 'Deleted equipment with success.');
    }

    public function restore(): ResponseInterface
    {
        try{
            return $this->__restore__();
        } catch (Throwable $e){
            return $this->throwError(500, $e);
        }
    }

    public function __restore__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_restore');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

        $data = ['id' => $this->request->getVar('id')];
        $rules = [
            'id' => [
                'rules' => [
                    'required',
                    'is_not_unique[equipments.id]'
                ],
                'errors' => [
                    'required' => 'Equipment ID is required.',
                    'is_not_unique' => 'Equipment with ID "{value}" doesn\'t exist.'
                ]
            ]
        ];

        if (!$this->validateData($data, $rules)){
            $errors = $this->validator->getErrors();
            return $this->fail([], 400, $errors['id']);
        }
        model('EquipmentModel')->restore($data['id']);
        return $this->buildAPIResponse(200, [], 'Restored equipment with success.');
    }

    public function batchCreate(): ResponseInterface
    {
        try{
            return $this->__batchCreate__();
        } catch (Throwable $e){
            return $this->throwError(500, $e);
        }
    }

    protected function __batchCreate__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_batch_register');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }
        $validator = (new BatchRegisterValidator())->addRequest($this->request);
        if ($validator->validate()){
            $formData = $validator->getValidatedData();
            $equipValidator = EquipmentValidator::getFromTypeName($formData['type_name']);
            // Needs to rename the fields because the front end fields
            // have user friendly names
            $res = model('EquipmentModel')->insertFromFile(
                $formData['file']->getTempName(),
                $equipValidator
            );
            if ($res === true){
                return $this->buildAPIResponse(200);
            }
            $iRow = remove_value('iRow', $res);
            $errorMsg = sprintf(
                'Validation error in entry %s.',
                $iRow
            );
            return $this->fail($res, 400, $errorMsg);
        }
        return $this->fail($validator->getErrors(), 400, 'Invalid data.');

    }

    /**
     * Disassembles an equipment in 3 diferent ways:
     * The params bellow are actually extracted from the request.
     *
     * @param string $id Equipment Id to operate on
     * @param string $operation Has 3 possibilities
     * 'children': removes all the children from equipment $id
     * 'parent': removes equipment $id from its parent
     * 'all': applies both conditions
     * 
     * @return ResponseInterface
     */ 
    public function disassemble(): ResponseInterface
    {
        try{
            return $this->__disassemble__();
        } catch (Throwable $e){
            return $this->fail($e->getMessage(), 500);
        }
    }

    protected function __disassemble__(): ResponseInterface
    {
        $requestInput = [
            'parent_id' => $this->request->getVar('parent_id'),
            'child_id' => $this->request->getVar('child_id')
        ];
        $validator = \Config\Services::validation();
        $rules = [
            'child_id' => [
                'rules' => [
                    'required',
                    'is_not_unique[equipments.id]'
                ],
                'errors' => [
                    'required' => 'Mising child_id parameter.',
                    'is_not_unique' => 'No equipment with ID {value}.'
                ]
            ],
            'parent_id' => [
                'rules' => [
                    'required',
                    'is_not_unique[equipments.id]'
                ],
                'errors' => [
                    'required' => 'Mising parent_id parameter.',
                    'is_not_unique' => 'No equipment with ID {value}.'
                ]
            ],
        ];
        if (!$validator->setRules($rules)->run($requestInput)){
            return $this->failValidationErrors($validator->getErrors());
        }
        model('EquipmentModel')->disassemble(
            $requestInput['parent_id'],
            $requestInput['child_id']
        );
        return $this->respond(['status' => 200], 200);
    }
}