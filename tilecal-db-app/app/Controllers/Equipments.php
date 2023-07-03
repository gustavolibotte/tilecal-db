<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EquipmentModel;
use App\Models\FileModel;

class Equipments extends BaseController
{
    public function index($modelName=null)
    {   
        $equipmentModel = new EquipmentModel();
        $headerData['pageTitle'] = 'Equipment Index';
        if (is_null($modelName)){
            $indexData['equipments'] = $equipmentModel->getEquipment();
        }
        else{
            $indexData['equipments'] = $equipmentModel->getEquipmentsWhereModel($modelName);
        }
        return view('templates/header', $headerData)
              .view('equipment/index', $indexData)
              .view('templates/footer');
    }

    public function view($id){
        $equipmentModel = new EquipmentModel();
        $viewData['equipments'] = $equipmentModel->getEquipment();
        $viewData['equipmentInfo'] = $equipmentModel->getEquipment($id);

        $fileModel = new FileModel();
        $viewData['files'] = $fileModel->getBindedFilesInfo($id);


        $headerData['pageTitle'] = sprintf('Equipment %s', $id);

        return view('templates/header', $headerData)
              .view('equipment/view', $viewData)
              .view('templates/footer');
    }
}
