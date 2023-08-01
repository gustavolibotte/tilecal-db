<?php

namespace App\Database\Seeds;

class SeedDatabase extends BaseSeeder
{
    public function run()
    {
        $this->seedModels();
        $this->seedStatuses();
    }

    public function seedModels(){
        $jsonData = $this->readJSON('models.json');
        $modelModel = new \App\Models\ModelModel(isMigration: true);
        foreach ($jsonData as $row){
            $row["params"] = json_encode($row['params']);
            $modelModel->insert($row);
        }
    }

    public function seedStatuses(){
        $jsonData = $this->readJSON('statuses.json');
        $statusModel = new \App\Models\StatusModel(isMigration: true);
        foreach ($jsonData as $row){
            $status = new \App\Entities\Status($row);
            $statusModel->insert($status);
        }
    }
}
