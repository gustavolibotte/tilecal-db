<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EquipmentModel;

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
}
