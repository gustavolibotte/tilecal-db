<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\ModelModel;

class ModelSeeder extends Seeder
{   
    public function run()
    {   
        $seedDataPath = '/root/tilecal-db/app/Database/Seeds/seed_data/models.json';
        $jsonString = file_get_contents($seedDataPath);
        $jsonData = json_decode($jsonString, true);
        $modelModel = new ModelModel();
        foreach ($jsonData as $row){
            $row["params"] = json_encode($row['params']);
            $modelModel->insert($row);
        }
    }
}
