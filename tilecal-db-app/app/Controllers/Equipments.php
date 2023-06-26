<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EquipmentModel;
use App\Models\ModelModel;

class Equipments extends BaseController
{
    public function index($modelName=null)
    {   
        $equipmentModel = new EquipmentModel();
        $headerData['pageTitle'] = 'Tilecal Database Equipment Index';
        if (is_null($modelName)){
            $indexData['equipments'] = $equipmentModel->getEquipment();
        }
        else{
            $formattedModelName = ModelModel::formatModelName($modelName);
            $headerData['pageTitle'] = str_replace('Equipment',$formattedModelName ,$headerData['pageTitle']);
            $indexData['equipments'] = $equipmentModel->getEquipmentsWhereModel($modelName);
        }
        return view('templates/header', $headerData)
              .view('equipment/index', $indexData)
              .view('templates/footer');
    }

    public function fetchEquipmentsTable()
    {   
        $equipmentModel = new EquipmentModel();
        $data = $equipmentModel->getEquipmentWithModelName();
        $response = $this->buildDataTablesResponse($data);
        echo $response;
    }

    public function registerGet(){

        $headerData['pageTitle'] = 'Register Equipment Tilecal Database';
        $equipmentModel = new EquipmentModel();
        $registerData['equipments'] = $equipmentModel->getEquipment();
        $modelModel = new ModelModel();
        $models = $modelModel->getNames();
        foreach ($models as &$model) {
            $model['formattedName'] = ModelModel::formatModelName($model['name']);
        }
        $registerData['models'] = $models;

        return view('templates/header', $headerData)
              .view('equipment/register', $registerData)
              .view('templates/footer');
    }

    public function registerPost() {

        helper('form');

    }
}
