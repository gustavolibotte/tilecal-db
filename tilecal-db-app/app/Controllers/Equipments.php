<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EquipmentModel;
use App\Models\EquipmentModelModel;

class Equipments extends BaseController
{
    public function index()
    {   
        $equipmentModel = new EquipmentModel();
        $headerData['pageTitle'] = 'Equipment Index';
        $indexData['equipments'] = $equipmentModel->getEquipment();
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
        $modelModel = new EquipmentModelModel();
        $models = $modelModel->getNames();
        foreach ($models as &$model) {
            $model['formattedName'] = EquipmentModelModel::formatModelName($model['name']);
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
