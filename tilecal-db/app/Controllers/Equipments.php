<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Equipment2FileModel;
use App\Models\EquipmentModel;
use App\Models\FileModel;
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
        $equipmentModel = new EquipmentModel();
        $equipmentInfo = $equipmentModel->getEquipment($equipmentId);
        $viewData['infoHeader'] = 'Equipment Info';
        $viewData['equipmentInfo'] = $equipmentInfo;
        $histModel = new \App\Models\HistModel();
        $viewData["histories"] = $histModel->getEquipmentHist($equipmentId);
        $viewData['pageHeader'] = sprintf('Equipment: %s', $equipmentInfo->barcode);
        $viewData['pageTitle'] = sprintf('Equipment %s', $equipmentInfo->barcode);
        return view('equipment/view', $viewData);
    }

    public function register($modelName='pmt'){
        helper(['form', 'string', 'url']);

        $formClass = sprintf('\App\Library\Forms\%sForm', snake2LowerCamelCase($modelName));
        $session = \Config\Services::session();
        $equipmentModel = new EquipmentModel();

        if ($this->request->is('post')){
            $form = new $formClass($this->request);
            if ($form->validate()){
                echo "Success";
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
}
