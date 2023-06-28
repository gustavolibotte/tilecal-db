<?php

namespace App\Controllers;

use App\Models\FenicsModel;
use App\Models\EquipmentModel;

class Fenics extends Equipments
{   
    public $modelName = "FENICS";
    public $snakeCaseModelName = "fenics";

    public function index()
    {
        $equipmentModel = new FenicsModel();
        $headerData = $this->getHeaderData(sprintf('%s Index', $this->modelName));
        $indexData['equipments'] = $equipmentModel->getEquipment();
        $indexData['model'] = [
            "name" => $this->modelName
        ];
        return view('templates/header', $headerData)
              .view('equipment/fenics/index', $indexData)
              .view('templates/footer');
    }

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
