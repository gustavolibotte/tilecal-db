<?php

namespace App\Database\Seeds;

class SeedDatabase extends BaseSeeder
{
    public function run()
    {
        $this->seedModels();
        $this->seedStatuses();
        $this->seedLocations();
        $this->seedFiles();
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

    public function seedLocations(){
        $jsonData = $this->readJSON('location.json');
        $locationModel = new \App\Models\LocationModel(isMigration: true);
        foreach ($jsonData as $row){
            $location = new \App\Entities\Location($row);
            $locationModel->insert($location);
        }
    }

    public function seedFiles(){
        $jsonData = $this->readJSON('files.json');
        foreach ($jsonData as $fileData){
            $fileData['filedata'] = file_get_contents(ROOTPATH . $fileData['filedata']);
            $file = new \App\Entities\File($fileData);
            $fileModel = new \App\Models\FileModel(isMigration: true);
            $fileModel->insert($file);
            if (str_starts_with($fileData['name'], 'deleted')){
                $fileModel->delete($file->id);
            }
        }
    }

    public function seedEquipments(){
        $modelModel = new \App\Models\ModelModel(isMigration: true);
        $models = $modelModel->findAll();

        $locationModel = new \App\Models\LocationModel(isMigration: true);
        $locations = $locationModel->findAll();

        $statusModel = new \App\Models\StatusModel(isMigration: true);
        $statuses = $statusModel->findAll();
        
        $jsonData = $this->readJSON('equipments.json');

        $equipmentModel = new \App\Models\EquipmentModel(isMigration: true);
        $randomModelParams = [
            "beta" => 123123,
            "nominal_hv" => 123123123,
            "qe" => 123123123,
            "type" => 'R11187'
        ];

        helper('random');
        foreach ($jsonData as $row) {
            $equipment = new \App\Entities\Equipment($row);
            $model = choice($models);
            $equipment->modelId = $model->id;
            $location = choice($locations);
            $equipment->locationId = $location->id;
            $status = choice($statuses);
            $equipment->statusId = $status->id;
            $equipment->modelParams = $randomModelParams;
            $equipmentModel->insert($equipment);
            if ($this->probDecision(0.03)){
                $equipmentModel->delete($equipment->id);
            }
        }
    }

}
