<?php

namespace App\Controllers;

use App\Models\FenicsModel;

class Fenics extends Equipments
{   
    public $modelName = "FENICS";
    public function index()
    {
        $fenicsModel = new FenicsModel();
        $headerData['pageTitle'] = sprintf('%s Index', $this->modelName);
        $indexData['equipments'] = $fenicsModel->getEquipment();
        $indexData['model'] = [
            "name" => $this->modelName
        ];
        return view('templates/header', $headerData)
              .view('equipment/fenics/index', $indexData)
              .view('templates/footer');
    }
}
