<?php

namespace App\Database\Seeds;

use App\Models\ModelModel;

class ModelSeeder extends BaseSeeder
{   
    public function run()
    {   
        $seedDataPath = ROOTPATH . '/app/Database/Seeds/seed_data/models.json';
        $jsonString = file_get_contents($seedDataPath);
        $jsonData = json_decode($jsonString, true);
        $modelModel = new ModelModel(migration: true);
        foreach ($jsonData as $row){
            $row["params"] = json_encode($row['params']);
            $modelModel->insert($row);
        }
    }
}
