<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EquipmentModel;
use App\Models\ModelModel;

class Equipments extends BaseController
{   
    public function index($modelName=null, $searchDeleted=false)
    {   
        $equipmentModel = new EquipmentModel();
        if (is_null($modelName)){
            $indexData['equipments'] = $equipmentModel->getEquipment(searchDeleted: $searchDeleted);
        }
        else{
            $indexData['equipments'] = $equipmentModel->getEquipmentsWhereModel($modelName, searchDeleted: $searchDeleted);
        }
        $indexData['pageTitle'] = 'Equipment Index';
        $indexData['pageHeader'] = 'Equipment Index';
        return view('equipment/index', $indexData);
    }

    public function view($equipmentId){
        helper('form');
        $equipmentModel = new EquipmentModel();
        $equipment = $equipmentModel->getEquipment($equipmentId);
        $viewData['infoHeader'] = 'Equipment Info';
        $viewData['equipmentInfo'] = $equipment;
        $histModel = new \App\Models\HistModel();
        $viewData["histories"] = $histModel->getEquipmentHist($equipmentId);
        $viewData['pageHeader'] = sprintf('Equipment: %s', $equipment->id);
        $viewData['pageTitle'] = sprintf('Equipment %s', $equipment->id);
        $viewData['editUrl'] = base_url(sprintf('equipments/%s/edit/%s', $equipment->modelName, $equipment->id));
        // $formClass = $this->getFormClass($equipment->modelName);
        $viewData['editForm'] = new \App\Library\Forms\EquipmentForm();
        $viewData['editForm']->asEdit();
        $viewData['editForm']->setFieldValues($equipment->toRawArray());
        $viewData['editable'] = true;
        return view('equipment/view', $viewData);
    }

    protected function getFormClass($modelName){
        helper('string');
        $formClass = sprintf('\App\Library\Forms\%sForm', snake2LowerCamelCase($modelName));
        return $formClass;
    }

    public function register($modelName='pmt'){
        helper(['form', 'url']);

        $formClass = $this->getFormClass($modelName);
        $session = \Config\Services::session();
        $equipmentModel = new EquipmentModel();

        if ($this->request->is('post')){
            $form = new $formClass($this->request);
            if ($form->validate()){
                $equipmentModel = new EquipmentModel();
                $equipment = $form->getEntity();
                $equipmentModel->insert($equipment);
                $session->setFlashdata('success', true);
                return redirect(sprintf('equipments/%s/register', $modelName));
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
        $registerData['successMessage'] = $session->getFlashdata('success');
        $registerData['formUrl'] = uri_string();
        $registerData['cancelUrl'] = base_url(sprintf('equipments/%s', $modelName));
        return view('equipment/register', $registerData);
    }

    public function edit($equipmentId, $modelName){
        $formClass = '\App\Library\Forms\EquipmentForm';
        $session = \Config\Services::session();
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
                $session->setFlashdata('messages', $messages);
            } else{
                foreach ($form->getErrors() as $errMsg){
                    $messages[] = [
                        'message' => $errMsg,
                        'type' => 'danger'
                    ];
                }
                $session->setFlashdata('messages', $messages);
            }
            return redirect()->to(base_url(sprintf('equipments/view/%s', $equipmentId)));
    }
}
