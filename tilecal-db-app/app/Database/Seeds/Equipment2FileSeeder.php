<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\Equipment2FileModel;

class Equipment2FileSeeder extends Seeder
{
    public function run()
    {
        $seedDataPath = '/root/tilecal-db/app/Database/Seeds/seed_data/equipments2files.csv';
        $csv = array_map("str_getcsv", file($seedDataPath, FILE_SKIP_EMPTY_LINES));
        $keys = array_shift($csv);
        $equipment2FileModel = new Equipment2FileModel();
        foreach ($csv as $row) {
            $data = array_combine($keys, $row);
            $equipment2FileModel->insert($data);
        }
    }
}
