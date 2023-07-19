<?php

namespace App\Database\Seeds;

use App\Models\EquipmentModel;
use App\Entities\Equipment;

class EquipmentSeeder extends BaseSeeder
{
    public function run()
    {   
        $seedDataPath = ROOTPATH . '/app/Database/Seeds/seed_data/equipments.csv';
        $csv = array_map("str_getcsv", file($seedDataPath, FILE_SKIP_EMPTY_LINES));
        $keys = array_shift($csv);
        $equipmentModel = new EquipmentModel();
        $randomModelParams = [
            "beta" => 123123,
            "nominal_hv" => 123123123,
            "qe" => 123123123,
            "type" => 'R11187'
        ];
        foreach ($csv as $row) {
            $data = array_combine($keys, $row);
            $equipment = new Equipment($data);
            $equipment->model_params = $randomModelParams;
            $equipmentModel->insert($equipment);
            if ($this->probDecision(0.03)){
                $equipmentModel->delete($equipment->id);
            }
        }
    }
}
