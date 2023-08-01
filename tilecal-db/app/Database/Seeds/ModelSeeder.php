<?php

namespace App\Database\Seeds;

use App\Models\ModelModel;

class ModelSeeder extends BaseSeeder
{   
    public function run()
    {   
        $jsonData = $this->readJSON('models.json');
        $modelModel = new ModelModel(isMigration: true);
        foreach ($jsonData as $row){
            $row["params"] = json_encode($row['params']);
            $modelModel->insert($row);
        }
    }
}
