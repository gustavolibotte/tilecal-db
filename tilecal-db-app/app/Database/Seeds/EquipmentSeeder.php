<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\EquipmentModel;
use App\Entities\Equipment;

class EquipmentSeeder extends Seeder
{
    public function run()
    {   
        $seedDataPath = '/root/tilecal-db/app/Database/Seeds/seed_data/equipments.csv';
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
            // $data = array_merge($data, $randomModelParams);
            // $data['model_params'] = json_encode($randomModelParams);
            $model = new Equipment($data);
            $model->model_params = $randomModelParams;
            $equipmentModel->insert($model);
        }
    }
}
