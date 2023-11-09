<?php

namespace App\Controllers;

use App\Library\Forms\EquipmentBatchRegisterForm;
use App\Library\Forms\EquipmentForm;
use App\Models\EquipmentModel;
use App\Models\EquipmentTypeModel;
use App\Validation\Validators\EquipmentValidator;
use CodeIgniter\HTTP\ResponseInterface;
use ValueError;

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
                ->select('label')
                ->select('active')
                ->where('name', $typeName)
                ->first();
            if (is_null($equipmentType)){
                return $this->notFound(
                    sprintf("Equipment type with name %s dowsn't exist", $typeName)
                );
            } elseif (!$equipmentType->active){
                return $this->serviceUnavailable();
            }
            $pageTitle = sprintf('%s Index', $equipmentType->label);
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
        $equipment = $equipmentModel->withDeleted()->getEquipment($equipmentId);

        if (is_null($equipment)){
            return redirect('notfound');
        } elseif ($equipment->isDeleted() && !$this->user->isAuthorized('delete')){
            return redirect('deleted-entry');
        }

        $viewData['infoHeader'] = 'Equipment Info';
        $viewData['entity'] = $equipment;
        // $viewData["histories"] = model('HistModel')->getEquipmentHist($equipmentId);
        $viewData['pageHeader'] = sprintf('Equipment: %s', $equipment->id);

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
            $parentRelation = $equipmentModel->getParentRelation($equipment->id);
            if (!empty($parentRelation)){
                $viewData['editForm']->setFieldValues([
                    'parent_id' => $parentRelation->parent_equip_id,
                    'slot_id' => $parentRelation->slot_id
                ]);
            }
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

        return default_view('equipment/view', $viewData);
    }

    public function batchRegister()
    {
        $feature = model('FeatureModel')->findName('equipment_batch_register');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }

        helper(['form', 'url', 'app_form', 'path']);
        $messenger = \Config\Services::messenger();
        if ($this->request->is('post')){
            $form = new EquipmentBatchRegisterForm($this->request);
            if ($form->validate()){
                $formData = $form->getValidatedData();
                $equipValidator = EquipmentValidator::getFromTypeName($formData['type_name']);
                // Needs to rename the fields because the front end fields
                // have user friendly names
                $colRename = [
                    'owner_institute' => 'owner_mne',
                    'location' => 'location_name',
                    'status' => 'status_name',
                    'owner' => 'owner_mne'
                ];
                $fileValidationResult = $equipValidator->validateFileData(
                    $formData['file']->getTempName(),
                    $colRename,
                    'EquipmentModel'
                );
                if ($fileValidationResult['status']) {
                    $messenger->addRegisterSuccess();
                    $form->clear();
                } else{
                    $filepath = $fileValidationResult['logFilepath'];
                    $filename = splitPath($filepath)['tail'];
                    $messenger->addMessage(sprintf(
                        'Found errors in the file data. ' .
                        '<a href="%s">Download validation log.</a>',
                        url_to('download_validation_log', $filename)
                        ),
                        'danger'
                    );
                }
            } else{
                foreach ($form->getErrors() as $errorMsg){
                    $messenger->addMessage($errorMsg, 'danger');
                }
            }
        } else{
            $form = new EquipmentBatchRegisterForm();
        }
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

        $equipmentModel = model('equipmentModel');
        $messenger = service('messenger');
        if ($this->request->is('post')){
            $validator = EquipmentValidator::getFromTypeName($typeName);
            $validator->addRequest($this->request);
            $form = EquipmentForm::getTypeForm($typeName, $this->request);
            if ($validator->validate()){
                $equipment = $validator->getValidatedData();
                $equipmentModel->insert($equipment);
                $messenger->addRegisterSuccess();
                $messenger->dumpMessages();
                return redirect()->route('equipment_register', [$typeName]);
            } else {
                foreach ($form->getErrors() as $errorMsg){
                    $messenger->addMessage($errorMsg, 'danger');
                }
                $messenger->dumpMessages();
            };
        } else{
            $form = EquipmentForm::getTypeForm($typeName);
        }
        $equipmentTypeModel = model('EquipmentTypeModel');
        $type = $equipmentTypeModel->where(sprintf("name = '%s'", $typeName))->first();
        $registerData['model'] = $type;
        $title = sprintf('Register %s', $type->label);
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
        $equipment = model('EquipmentModel')->fullQuery()->find($equipmentId);
        $form = EquipmentForm::getTypeForm($equipment->type_name, $this->request);
        $form->asEdit();
        $messenger = service('messenger');
        if ($form->validate()){
            $equipment = $form->getValidatedData();
            $equipmentModel->update($equipmentId, $equipment);
            $messenger->addMessage(
                sprintf('Edited equipment %s with success', $equipment->id),
                'success'
            );
        } else{
            foreach ($form->getErrors() as $errorMsg){
                $messenger->addMessage($errorMsg, 'danger');
            }
        }
        $messenger->dumpMessages();
        return redirect()->route('equipment_view', [$equipmentId]);
    }

    public function delete(string $equipmentId)
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

    public function restore(string $equipmentId)
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

    public function __get__(): ResponseInterface
    {
        $feature = model('FeatureModel')->findName('api_equipment_get');
        if (!$feature->isActive()){
            return $this->fail("", 503);
        }

        $equipmentModel = model('EquipmentModel')->asArray();
        $equipmentModel = $this->parseFilterCondition('id', 'equipments.id', $equipmentModel);
        $equipmentModel = $this->parseFilterCondition('typeName', 'equipment_types.name', $equipmentModel);

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
        $res = [
            'status' => 'success'
        ];
        $res['data'] = empty($data)? []: $data;
        return $this->respond($res, 200);
    }

    public function __getHist__(): ResponseInterface
    {
        $id = $this->request->getVar('id');
        if (empty($id)){
            throw new ValueError('An id value must be given.');
        }
        $history = model('HistModel')
            ->getEquipmentHist($id);
        $res = [
            'status' => 'success',
            'data' => $history
        ];
        return $this->respond($res, 200);
    }
}