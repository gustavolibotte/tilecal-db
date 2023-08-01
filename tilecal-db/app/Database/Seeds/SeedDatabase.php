<?php

namespace App\Database\Seeds;

class SeedDatabase extends BaseSeeder
{
    public function run()
    {   
        helper('random');
        $this->seedModels();
        $this->seedStatuses();
        $this->seedLocations();
        $this->seedFiles();
        $this->seedEquipments();
        $this->seedEquipments2Files();
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
        $jsonData = $this->readJSON('locations.json');
        $locationModel = new \App\Models\LocationModel(isMigration: true);
        foreach ($jsonData as $row){
            $location = new \App\Entities\Location($row);
            $locationModel->insert($location);
        }
    }

    public function seedFiles(){
        $jsonData = $this->readJSON('files.json');
        // d($jsonData);
        foreach ($jsonData as $fileData){
            $fileData['filedata'] = $this->readFile($fileData['filedata']);
            $file = new \App\Entities\File($fileData);
            // d($file->name);
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

        $i = 0;
        foreach ($jsonData as $row) {
            $equipment = new \App\Entities\Equipment($row);
            $model = random_choice($models);
            $equipment->model_id = $model->id;
            $location = random_choice($locations);
            $equipment->location_id = $location->id;
            $status = random_choice($statuses);
            $equipment->status_id = $status->id;
            $equipment->model_params = $randomModelParams;
            $equipmentModel->insert($equipment);
            if ($this->probDecision(0.03)){
                $equipmentModel->delete($equipment->id);
            }
        }
    }

    public function seedEquipments2Files(){
        $equipmentModel = new \App\Models\EquipmentModel(isMigration: true);
        $equipments = $equipmentModel->findAll();
        $fileModel = new \App\Models\FileModel(isMigration: true);
        $files = $fileModel->findAll();

        $equipments2FileModel = new \App\Models\Equipment2FileModel(isMigration: true);
        foreach ($equipments as $equipment){
            $file = random_choice($files);
            $data = [
                'equipment_id' => $equipment->id,
                'file_id' => $file->id
            ];
            $equipment2File = new \App\Entities\Equipment2File($data);
            $equipments2FileModel->insert($equipment2File);
        }
    }

}
