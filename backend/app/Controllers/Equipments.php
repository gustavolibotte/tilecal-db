<?php

namespace App\Controllers;

use App\Validation\Validators\BatchRegisterValidator;
use App\Validation\Validators\EquipmentAssembleValidator;
use App\Validation\Validators\EquipmentValidator;
use CodeIgniter\HTTP\ResponseInterface;
use ValueError;
use Throwable;

class Equipments extends APIController
{
    public function get(): ResponseInterface
    {
        try{
            return $this->__get__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    protected function __get__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('api_equipment_get');
        if (!$feature->isActive()){
            return $this->failServiceUnavailable();
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
                return $this->failForbidden();
            }
            $equipmentModel = $equipmentModel->onlyDeleted();
        } elseif (!empty($this->request->getVar('withDeleted'))){
            if (!empty($deleteMsg)){
                return $this->failForbidden();
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
        return $this->respondQueryResults($data);
    }

    public function post(): ResponseInterface
    {
        try{
            return $this->__post__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    protected function __post__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_register');
        if (!$feature->isActive()){
            return $this->failServiceUnavailable();
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
            return $this->failInvalidParams($errors);
        }

        $validator = EquipmentValidator::getFromTypeName($data['type_name']);
        $validator->addRequest($this->request);
        if ($validator->validate()){
            $equipment = $validator->getValidatedData();
            $equipmentModel = model('EquipmentModel');
            $equipmentModel->insert($equipment);
            return $this->respondCreated($equipment->id);
        }else{
            $errors = $validator->getErrors();
            return $this->failValidationErrors($errors);
        }
    }

    public function getHist(): ResponseInterface
    {
        try{
            return $this->__getHist__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    protected function __getHist__(): ResponseInterface
    {
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
            return $this->failInvalidParams($errors);
        }
        $history = model('HistModel')
            ->getEquipmentHist($data['id']);
        return $this->respondQueryResults($history);
    }

    public function put(): ResponseInterface
    {
        try{
            return $this->__put__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    protected function __put__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_edit');
        if (!$feature->isActive()){
            return $this->failServiceUnavailable();
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
            return $this->failInvalidParams($errors);
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
            return $this->respondUpdated($data['id']);
        }
        $errors = $validator->getErrors();
        return $this->failValidationErrors($errors);
    }

    public function delete(): ResponseInterface
    {
        try{
            return $this->__delete__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    protected function __delete__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_delete');
        if (!$feature->isActive()){
            return $this->failServiceUnavailable();
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
            return $this->failInvalidParams($errors);
        }
        model('EquipmentModel')->delete($data['id']);
        return $this->respondDeleted($data['id']);
    }

    public function restore(): ResponseInterface
    {
        try{
            return $this->__restore__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    public function __restore__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_restore');
        if (!$feature->isActive()){
            return $this->failServiceUnavailable();
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
            return $this->failInvalidParams($errors);
        }
        model('EquipmentModel')->restore($data['id']);
        return $this->respondRestored($data['id']);
    }

    public function batchCreate(): ResponseInterface
    {
        try{
            return $this->__batchCreate__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    protected function __batchCreate__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('equipment_batch_register');
        if (!$feature->isActive()){
            return $this->failServiceUnavailable();
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
                return $this->respondCreated();
            }
            $res['row'] = remove_value('iRow', $res);
            return $this->failValidationErrors($res, 'Invalid row.');
        }
        return $this->failValidationErrors($validator->getErrors());

    }

    public function disassemble(): ResponseInterface
    {
        try{
            return $this->__disassemble__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    protected function __disassemble__(): ResponseInterface
    {
        $data = [
            'parent_id' => $this->request->getVar('parent_id'),
            'child_id' => $this->request->getVar('child_id')
        ];
        $validator = \Config\Services::validation();
        $rules = [
            'child_id' => [
                'rules' => [
                    'required',
                    'is_not_unique[parent_child_equips.child_equip_id]'
                ],
                'errors' => [
                    'required' => 'Mising child_id parameter.',
                    'is_not_unique' => 'The equipment {value} ID is not assembled to any equipment.'
                ]
            ],
            'parent_id' => [
                'rules' => [
                    'required',
                    'is_not_unique[parent_child_equips.parent_equip_id]'
                ],
                'errors' => [
                    'required' => 'Mising parent_id parameter.',
                    'is_not_unique' => 'The equipment {value} ID has no equipment assembled to it.'
                ]
            ],
        ];
        if ($validator->setRules($rules)->run($data)){
            model('EquipmentModel')->disassemble(
                $data['parent_id'],
                $data['child_id']
            );
            return $this->buildAPIResponse(200, message: 'Disassembled');
        }
        return $this->failValidationErrors($validator->getErrors());
    }

    public function assemble()
    {
        try{
            return $this->__assemble__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    protected function __assemble__()
    {
        $feature = model('FeatureModel')->findName('parent_relation');
        if (!$feature->isActive()){
            return $this->failServiceUnavailable();
        }
        $validator = (new EquipmentAssembleValidator())->addRequest($this->request);
        if ($validator->validate()){
            $validatedData = $validator->getValidatedData();
            model('EquipmentModel')->updateParent(
                $validatedData['child_id'],
                $validatedData['parent_id'],
                $validatedData['slot_id']
            );
            return $this->buildAPIResponse(200, message:'Assembled');
        }
        return $this->failValidationErrors($validator->getErrors());
    }
}