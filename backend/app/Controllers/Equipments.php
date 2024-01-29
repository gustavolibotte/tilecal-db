<?php

namespace App\Controllers;

use App\Library\Forms\AssembleForm;
use App\Library\Forms\EquipmentBatchRegisterForm;
use App\Library\Forms\EquipmentForm;
use App\Validation\Validators\BatchRegisterValidator;
use App\Validation\Validators\EquipmentAssembleValidator;
use App\Validation\Validators\EquipmentValidator;
use CodeIgniter\HTTP\ResponseInterface;
use ValueError;
use Throwable;

class Equipments extends APIController
{

    public function index($typeName=null)
    {   
        $feature = model('FeatureModel')->findName('equipment_index');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }
        $typeName = $this->request->getVar('typeName');
        if (!empty($this->request->getVar('onlyDeleted'))){
            $indexData['headerClass'] = 'text-danger';
            $pageTitle = 'Deleted Equipment Index';
        } elseif (!empty($typeName)){
            $indexData['typeName'] = $typeName;
            $equipmentType = model('EquipmentTypeModel')
                ->select('name')
                ->select('active')
                ->where('name', $typeName)
                ->first();
            if (is_null($equipmentType)){
                return $this->notFound(
                    sprintf("Equipment type with name %s doesn't exist", $typeName)
                );
            } elseif (!$equipmentType->active){
                return $this->serviceUnavailable();
            }
            $pageTitle = sprintf('%s Index', $equipmentType->name);
        } else{
            $pageTitle = 'Equipment Index';
        }
            $indexData['pageTitle'] = $pageTitle;
            $indexData['pageHeader'] = $pageTitle;
        
        return default_view('equipment/index', $indexData);
    }

    public function view(string $equipmentId){

        $feature = model('FeatureModel')->findName('equipment_view');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

        helper(['app_form', 'form']);
        $equipmentModel = model('EquipmentModel');
        $equipment = $equipmentModel->fullQuery()
            ->withDeleted()
            ->find($equipmentId);

        if (is_null($equipment)){
            return redirect('notfound');
        } elseif ($equipment->isDeleted() && !$this->user->isAuthorized('delete')){
            return redirect('deleted-entry');
        }

        $viewData['infoHeader'] = 'Equipment Info';
        $viewData['entity'] = $equipment;
        $viewData['pageHeader'] = sprintf(
            '%s: %s',
            $equipment->type_name,
            $equipment->id
        );

        if ($equipment->isDeleted()){
            $viewData['pageHeader'] = 'Deleted ' . $viewData['pageHeader'];
            $viewData['headerClass'] = 'text-danger';
        }
        $viewData['pageTitle'] = $viewData['pageHeader'];

        $viewData['editable'] = $this->user->isAuthorized('edit') && !$equipment->isDeleted();
        if ($viewData['editable']){
            $viewData['editForm'] = EquipmentForm::getTypeForm($equipment->type_name);
            $viewData['editForm']->asEdit();
            $viewData['editForm']->setFieldValues($equipment->toArrayWithTypeParams());
            $viewData['editUrl'] = url_to('equipment_edit', $equipment->id);

        }

        $viewData['deletable'] = $this->user->isAuthorized('delete') && !$equipment->isDeleted();
        if ($viewData['deletable']){
            $viewData['deleteUrl'] = url_to('equipment_delete', $equipment->id);
        }

        $viewData['restorable'] = $this->user->isAuthorized('delete') && $equipment->isDeleted();
        if ($viewData['restorable']){
            $viewData['restoreUrl'] = url_to('equipment_restore', $equipment->id);
        }

        $viewData['canBeAssembled'] = $this->user->isAuthorized('edit') && $equipmentModel->equipHasSlot($equipment->id);
        if ($viewData['canBeAssembled']){
            $viewData['assembleUrl'] = url_to('equipment_assemble', $equipmentId);
            $viewData['assembleForm'] = new AssembleForm($equipment);
        }
        return default_view('equipment/view', $viewData);
    }

    public function batchRegister()
    {
        $feature = model('FeatureModel')->findName('equipment_batch_register');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

        helper(['form', 'url', 'app_form', 'path', 'app_array']);
        $messenger = \Config\Services::messenger();
        if ($this->request->is('post')){
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
                    $messenger->addRegisterSuccess();
                    $validator->clearState();
                    $equipValidator->clearState();
                } else{
                    $iRow = remove_value('iRow', $res);
                    foreach ($res as $errorMsg){
                        $msg = sprintf(
                            'Row %d: %s',
                            $iRow, $errorMsg
                        );
                        $messenger->addMessage($msg, 'danger');
                    }
                }
            } else{
                foreach ($validator->getErrors() as $errorMsg){
                    $messenger->addMessage($errorMsg, 'danger');
                }
            }
        }
        $form = new EquipmentBatchRegisterForm($this->request);
        $messenger->dumpMessages();
        $viewData['forms'] = ['' => $form];
        $viewData['formUrl'] = uri_string();
        $viewData['pageTitle'] = 'Batch Register';
        $viewData['pageHeader'] = 'Batch Equipment Register';
        $viewData['cancelUrl'] = url_to('equipment_index');
        return default_view('equipment/batch_register', $viewData);
    }

    public function register($typeName='pmt'){

        $feature = model('FeatureModel')->findName('equipment_register');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

        helper(['form', 'url', 'app_form']);

        $equipmentModel = model('EquipmentModel');
        $messenger = service('messenger');
        if ($this->request->is('post')){
            $validator = EquipmentValidator::getFromTypeName($typeName);
            $validator->addRequest($this->request);
            if ($validator->validate()){
                $equipment = $validator->getValidatedData();
                $equipmentModel->insert($equipment);
                $messenger->addRegisterSuccess();
                $messenger->dumpMessages();
                return redirect()->route('equipment_register', [$typeName]);
            } else {
                foreach ($validator->getErrors() as $errorMsg){
                    $messenger->addMessage($errorMsg, 'danger');
                }
                $messenger->dumpMessages();
                $form = EquipmentForm::getTypeForm($typeName, $this->request);
            };
        } else{
            $form = EquipmentForm::getTypeForm($typeName);
        }
        $type = model('EquipmentTypeModel')->where(sprintf("name = '%s'", $typeName))->first();
        $registerData['model'] = $type;
        $title = sprintf('Register %s', $type->name);
        $registerData['pageTitle'] = $title;
        $registerData['pageHeader'] = $title;
        $registerData['forms'] = ['Equipment Info'=> $form];
        $registerData['formUrl'] = uri_string();
        $registerData['cancelUrl'] = url_to('equipment_index_type', $typeName);

        return default_view('equipment/register', $registerData);
    }

    public function edit($equipmentId)
    {
        $feature = model('FeatureModel')->findName('equipment_edit');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }
        $equipmentModel = model('EquipmentModel');
        $equipment =$equipmentModel->fullQuery()->find($equipmentId);
        $validator = EquipmentValidator::getFromTypeName($equipment->type_name)
            ->addRequest($this->request)
            ->asEdit();
        $messenger = service('messenger');
        if ($validator->validate()){
            $equipment = $validator->getValidatedData();
            $equipmentModel->update($equipmentId, $equipment);
            $messenger->addMessage(
                sprintf('Edited equipment %s with success', $equipment->id),
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

    public function oldDelete(string $equipmentId)
    {
        $feature = model('FeatureModel')->findName('equipment_delete');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

        $equipmentModel = model('EquipmentModel');
        $equipmentModel->delete($equipmentId);
        $messenger = service('messenger');
        $messenger->addDeleteSuccess();
        $messenger->dumpMessages();
        if ($this->user->isAuthorized('delete')){
            return redirect()->route('equipment_view', [$equipmentId]);
        } else {
            return redirect()->route('equipment_index');
        }
    }

    public function oldRestore(string $equipmentId)
    {
        $feature = model('FeatureModel')->findName('equipment_restore');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }
        $equipmentModel = model('EquipmentModel');
        $equipmentModel->restore($equipmentId);
        service('messenger')->addRestoreSuccess();
        return redirect()->route('equipment_view', [$equipmentId]);
    }

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