<?php

namespace App\Controllers;

use App\Models\FenicsModel;
use App\Models\EquipmentModel;

class Fenics extends Equipments
{   
    public $modelName = "FENICS";
    public $snakeCaseModelName = "fenics";

    public function registerGet(){

        helper('form');

        $headerData = $this->getHeaderData(sprintf('%s Register', $this->modelName));
        $equipmentModel = new EquipmentModel();
        $registerData['equipments'] = $equipmentModel->getSideTable();
        $registerData['model'] = [
            "name" => $this->modelName,
            "snakeCase" => $this->snakeCaseModelName
        ];

        return view('templates/header', $headerData)
              .view('equipment/no_child_register', $registerData)
              .view('templates/footer');
    }
}
