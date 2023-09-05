<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EquipmentModel;
use App\Models\EquipmentTypeModel;

class Equipments extends BaseController
{   
    public function index($typeName=null)
    {   
        $indexData['pageTitle'] = 'Equipment Index';
        $indexData['pageHeader'] = 'Equipment Index';
        $indexData['typeName'] = $typeName;
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
        $equipmentModel = new EquipmentModel();
        $equipment = $equipmentModel->withDeleted()->getEquipment($equipmentId);

        if (is_null($equipment)){
            return redirect('notfound');
        } elseif ($equipment->isDeleted() && !$this->user->isAuthorized('delete')){
            return redirect('deleted-entry');
        }

        $viewData['infoHeader'] = 'Equipment Info';
        $viewData['entity'] = $equipment;
        $viewData["histories"] = (new \App\Models\HistModel())->getEquipmentHist($equipmentId);
        $viewData['pageHeader'] = sprintf('Equipment: %s', $equipment->id);

        if ($equipment->isDeleted()){
            $viewData['pageHeader'] = 'Deleted ' . $viewData['pageHeader'];
            $viewData['headerClass'] = 'text-danger';
        }
        $viewData['pageTitle'] = $viewData['pageHeader'];

        $viewData['editable'] = $this->user->isAuthorized('edit') && !$equipment->isDeleted();
        if ($viewData['editable']){
            $viewData['editForm'] = new (get_model_form_class($equipment->type_name))();
            $viewData['editForm']->asEdit();
            $viewData['editForm']->setFieldValues($equipment->toArrayWithTypeParams());
            $viewData['editForm']->setFieldValues(['parent_id' => $equipmentModel->getParentId($equipment->id)]);
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

        $formClass = get_model_form_class($typeName);
        $equipmentModel = new EquipmentModel();
        $messenger = service('messenger');

        if ($this->request->is('post')){
            $form = new $formClass($this->request);
            if ($form->validate()){
                $equipmentModel = new EquipmentModel();
                $equipment = $form->getEntity();
                $equipmentModel->insert($equipment);
                $messenger->addRegiserSuccess();
                return redirect(sprintf('equipments/%s/register', $typeName));
            } else {
                foreach ($form->getErrors() as $errorMsg){
                    $messenger->addMessage($errorMsg, 'danger');
                }
            };
        } else{
            $form = new $formClass();
        }
        
        $equipmentTypeModel = new EquipmentTypeModel();
        $type = $equipmentTypeModel->where(sprintf("name = '%s'", $typeName))->first();
        $registerData['model'] = $type;
        $title = sprintf('Register %s', $type->label);
        $registerData['pageTitle'] = $title;
        $registerData['pageHeader'] = $title;
        $registerData['forms'] = ['Equipment Info'=> $form];
        $registerData['formUrl'] = uri_string();
        $registerData['successMessage'] = false;
        $registerData['cancelUrl'] = base_url(sprintf('equipments/%s', $typeName));
        $messenger->dumpMessages();
        
        return default_view('equipment/register', $registerData);
    }

    public function edit($equipmentId, $typeName)
    {
        helper(['app_form']);
        $formClass = get_model_form_class($typeName);
        $form = new $formClass($this->request);
        $form->asEdit();
        $messenger = service('messenger');
        if ($form->validate()){
            $equipmentModel = new EquipmentModel();
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
        $equipmentModel = new EquipmentModel();
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
}