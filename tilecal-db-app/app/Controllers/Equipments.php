<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EquipmentModel;

class Equipments extends BaseController
{
    public function index()
    {
        return view('equipment/table');
    }

    public function serverSideDataTables()
    {   
        $equipmentModel = new EquipmentModel();
        $data = $equipmentModel->equipmentsWithModelName();
        $response['draw'] = 1;
        $response['data'] = $data;
        // $response['recordsTotal'] = count($data);
        $response['recordsFiltered'] = count($data);
        echo json_encode($response);
    }
}
