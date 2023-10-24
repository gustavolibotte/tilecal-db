<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Library\Forms\EquipmentForm;
use App\Models\EquipmentModel;
use App\Models\EquipmentTypeModel;
use CodeIgniter\API\ResponseTrait;

class Equipments extends BaseController
{
    use ResponseTrait;
    
    public function index($typeName=null)
    {   
        $typeName = $this->request->getVar('type_name');
        if (!empty($this->request->getVar('deleted'))){
            $indexData['headerClass'] = 'text-danger';
            $pageTitle = 'Deleted Equipment Index';
        } elseif (!empty($typeName)){
            $indexData['typeName'] = $typeName;
            $equipmentType = model('EquipmentTypeModel')
                ->select('label')
                ->where('name', $typeName)
                ->first();
            $pageTitle = sprintf('%s Index', $equipmentType->label);
        } else{
            $pageTitle = 'Equipment Index';
        }
            $indexData['pageTitle'] = $pageTitle;
            $indexData['pageHeader'] = $pageTitle;
        
        return default_view('equipment/index', $indexData);
    }

    public function deletedIndex()
    {   
        $indexData['pageTitle'] = 'Deleted Equipment Index';
        $indexData['pageHeader'] = 'Deleted Equipment Index';
        $indexData['headerClass'] = 'text-danger';
        return default_view('equipment/deleted_index', $indexData);
    }

    public function view($equipmentId){
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
        $viewData["histories"] = model('HistModel')->getEquipmentHist($equipmentId);
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
            $viewData['editUrl'] = base_url(sprintf('equipments/%s/edit/%s', $equipment->type_name, $equipment->id));
        }

        $viewData['deletable'] = $this->user->isAuthorized('delete') && !$equipment->isDeleted();
        if ($viewData['deletable']){
            $viewData['deleteUrl'] = base_url(sprintf('equipments/delete/%s', $equipment->id));
        }

        $viewData['restorable'] = $this->user->isAuthorized('delete') && $equipment->isDeleted();
        if ($viewData['restorable']){
            $viewData['restoreUrl'] = base_url(sprintf('equipments/restore/%s', $equipment->id));
        }

        return default_view('equipment/view', $viewData);
    }

    public function register($typeName='pmt'){
        helper(['form', 'url', 'app_form']);

        $equipmentModel = new EquipmentModel();
        $messenger = service('messenger');
        if ($this->request->is('post')){
            $form = EquipmentForm::getTypeForm($typeName, $this->request);
            $fieldValues = $form->getFieldValues();
            if ($form->validate()){
                $equipmentModel = new EquipmentModel();
                $equipment = $form->getEntity();
                $equipmentModel->insert($equipment);
                $messenger->addRegisterSuccess();
                $messenger->dumpMessages();
                return redirect()->to(sprintf('equipments/%s/register', $typeName));
            } else {
                foreach ($form->getErrors() as $errorMsg){
                    $messenger->addMessage($errorMsg, 'danger');
                }
                $messenger->dumpMessages();
            };
        } else{
            $form = EquipmentForm::getTypeForm($typeName);
        }
        $equipmentTypeModel = new EquipmentTypeModel();
        $type = $equipmentTypeModel->where(sprintf("name = '%s'", $typeName))->first();
        $registerData['model'] = $type;
        $title = sprintf('Register %s', $type->label);
        $registerData['pageTitle'] = $title;
        $registerData['pageHeader'] = $title;
        $registerData['forms'] = ['Equipment Info'=> $form];
        $registerData['formUrl'] = uri_string();
        $registerData['cancelUrl'] = base_url(sprintf('equipments/%s', $typeName));
        
        return default_view('equipment/register', $registerData);
    }

    public function edit($equipmentId, $typeName)
    {
        $form = EquipmentForm::getTypeForm($typeName, $this->request);
        $form->asEdit();
        $messenger = service('messenger');
        if ($form->validate()){
            $equipmentModel = model('EquipmentModel');
            $equipment = $form->getEntity();
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
        return redirect()->to(base_url(sprintf('equipments/view/%s', $equipmentId)));
    }

    public function delete(string $equipmentId)
    {
        $equipmentModel = model('EquipmentModel');
        $equipmentModel->delete($equipmentId);
        $messenger = service('messenger');
        $messenger->addDeleteSuccess();
        $messenger->dumpMessages();
        if ($this->user->isAuthorized('delete')){
            return redirect()->to(base_url(sprintf('equipments/view/%s', $equipmentId)));
        } else {
            return redirect('equipments');
        }
    }

    public function restore(string $equipmentId)
    {
        $equipmentModel = new EquipmentModel();
        $equipmentModel->restore($equipmentId);
        service('messenger')->addRestoreSuccess();
        return redirect()->to(base_url(sprintf('equipments/view/%s', $equipmentId)));
    }

    public function get()
    {
        $msg = $this->checkAjaxRequest();
        if (!empty($msg)){
            return $this->fail($msg, 400);
        }
        $equipmentModel = model('EquipmentModel')->asArray();
        $equipmentModel = $this->addFilterCondition('id', 'equipments.id', $equipmentModel);
        $equipmentModel = $this->addFilterCondition('type_name', 'equipment_types.name', $equipmentModel);

        if (!empty($this->request->getVar('relatedFileId'))){
            $equipmentModel = $this->addFilterCondition(
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

        $relatedEquipmentId = ($this->request->getVar('relatedEquipmentId'));
        if (empty($relatedEquipmentId)){
            $equipmentModel = $equipmentModel->fullQuery();
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
}