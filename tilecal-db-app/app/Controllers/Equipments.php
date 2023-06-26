<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EquipmentModel;
use App\Models\ModelModel;

class Equipments extends BaseController
{
    public function index()
    {
        $equipmentModel = new EquipmentModel();
        $data['equipments'] = $equipmentModel->getEquipmentWithModelName();
        return view('templates/header').view('equipment/index', $data);
    }

    public function fetchEquipmentsTable()
    {   
        $equipmentModel = new EquipmentModel();
        $data = $equipmentModel->getEquipmentWithModelName();
        $response = $this->buildDataTablesResponse($data);
        echo $response;
    }

    public function register() {
        $equipmentModel = new EquipmentModel();
        $modelModel = new ModelModel();
        $modelName = $this->request->getVar('model_name');
        $receivedParentId = $this->request->getVar('parent_id');

        $data = [
            'id' => $this->request->getVar('id'),
            'model_id'  => $modelModel->getIdFromModelName($modelName),
            'location' => $this->request->getVar('location'),
            'status' => $this->request->getVar('status'),
            'parent_id' => ($receivedParentId == '') ? null: $receivedParentId
        ];
        $equipmentModel->insert($data);
        return $this->response->redirect(site_url('/equipments'));
    }
}
