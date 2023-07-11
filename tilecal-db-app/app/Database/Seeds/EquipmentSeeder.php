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
        foreach ($csv as $row) {
            $data = array_combine($keys, $row);
            $model = new Equipment($data);
            $equipmentModel->insert($model);
        }
    }
}
