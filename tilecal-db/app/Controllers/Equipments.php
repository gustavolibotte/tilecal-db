<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EquipmentModel;
use App\Models\Equipment2FileModel;
use App\Models\ModelModel;

class Equipments extends BaseController
{   
    public function index($modelName=null)
    {   
        $indexData['pageTitle'] = 'Equipment Index';
        $indexData['pageHeader'] = 'Equipment Index';
        return view('equipment/index', $indexData);
    }

    public function deletedIndex()
    {   
        $indexData['pageTitle'] = 'Deleted Equipment Index';
        $indexData['pageHeader'] = 'Deleted Equipment Index';
        $indexData['headerClass'] = 'text-danger';
        return view('equipment/deleted_index', $indexData);
    }

    public function view($equipmentId){
        helper(['app_form', 'form']);
        $equipmentModel = new EquipmentModel();
        $equipment = $equipmentModel->withDeleted()->getEquipment($equipmentId);

        if (is_null($equipment)){
            return redirect('notfound');
        } elseif ($equipment->isDeleted() and !$this->user->isAuthorized('delete')){
            return redirect('unauthorized');
        }

        $viewData['infoHeader'] = 'Equipment Info';
        $viewData['equipmentInfo'] = $equipment;
        $histModel = new \App\Models\HistModel();
        $viewData["histories"] = $histModel->getEquipmentHist($equipmentId);
        $viewData['pageHeader'] = sprintf('Equipment: %s', $equipment->id);
        $viewData['isDeleted'] = $equipment->isDeleted();
        if ($viewData['isDeleted']){
            $viewData['pageHeader'] = 'Deleted ' . $viewData['pageHeader'];
            $viewData['headerClass'] = 'text-danger';
        }
        $viewData['pageTitle'] = $viewData['pageHeader'];
        $viewData['editUrl'] = base_url(sprintf('equipments/%s/edit/%s', $equipment->model_name, $equipment->id));

        if ($this->user->isAuthorized('edit')){
            $viewData['editForm'] = new (get_model_form_class($equipment->model_name))();
            $viewData['editForm']->asEdit();
            $viewData['editForm']->setFieldValues($equipment->toArrayWithModelParams());
            $viewData['editable'] = true;
        } else{
            $viewData['editable'] = false;
        }

        if ($this->user->isAuthorized('delete')){
            $viewData['deleteUrl'] = base_url(sprintf('equipments/delete/%s', $equipment->id));
            $viewData['deletable'] = true;
        } else{
            $viewData['deletable'] = false;
        }

        return view('equipment/view', $viewData);
    }

    public function register($modelName='pmt'){
        helper(['form', 'url', 'message', 'app_form']);

        $formClass = get_model_form_class($modelName);
        $equipmentModel = new EquipmentModel();

        if ($this->request->is('post')){
            $form = new $formClass($this->request);
            if ($form->validate()){
                $equipmentModel = new EquipmentModel();
                $equipment = $form->getEntity();
                $equipmentModel->insert($equipment);
                $messages[] = [
                    'message' => 'Register success',
                    'type' => 'success'
                ];
                addMessages($messages);
                return redirect(sprintf('equipments/%s/register', $modelName));
            } else {
                foreach ($form->getErrors() as $errorMsg){
                    $messages[] = [
                        'message' => $errorMsg,
                        'type' => 'danger'
                    ];
                }
                addMessages($messages);
            };
        } else{
            $form = new $formClass();
        }
        
        $modelModel = new ModelModel();
        $model = $modelModel->where(sprintf("name = '%s'", $modelName))->first();
        $registerData['model'] = $model;
        $title = sprintf('Register %s', $model->label);
        $registerData['pageTitle'] = $title;
        $registerData['pageHeader'] = $title;
        $registerData['forms'] = ['Equipment Info'=> $form];
        $registerData['formUrl'] = uri_string();
        $registerData['successMessage'] = false;
        $registerData['cancelUrl'] = base_url(sprintf('equipments/%s', $modelName));
        
        return view('equipment/register', $registerData);
    }

    public function edit($equipmentId, $modelName)
    {
        helper(['message', 'app_form']);
        $formClass = get_model_form_class($modelName);
        $form = new $formClass($this->request);
        $form->asEdit();
        if ($form->validate()){
            $equipmentModel = new EquipmentModel();
            $equipment = $form->getEntity();
            $equipmentModel->update($equipmentId, $equipment);
            $messages[] = [
                'message' => sprintf('Edited equipment %s with success', $equipment->id),
                'type' => 'success'
            ];
            addMessages($messages);
        } else{
            foreach ($form->getErrors() as $errorMsg){
                $messages[] = [
                    'message' => $errorMsg,
                    'type' => 'danger'
                ];
                addMessages($messages);
            }
        }
        return redirect()->to(base_url(sprintf('equipments/view/%s', $equipmentId)));
    }

    public function delete($equipmentId)
    {
        helper('message');
        $equipmentModel = new EquipmentModel();
        $equipmentModel->deleteEquipment($equipmentId);
        $messages[] = [
            'message' => 'Deleted with success',
            'type' => 'success'
        ];
        addMessages($messages);
        return redirect('equipments');
    }
}