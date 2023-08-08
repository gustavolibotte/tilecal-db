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
        echo "Seeding Models\n";
        $jsonData = $this->readJSON('models.json');
        $modelModel = new \App\Models\ModelModel(isMigration: true);
        foreach ($jsonData as $row){
            $row["params"] = json_encode($row['params']);
            $modelModel->insert($row);
        }
        echo "Seeded Models\n";
    }

    public function seedStatuses(){
        echo "Seeding Statuses";
        $jsonData = $this->readJSON('statuses.json');
        $statusModel = new \App\Models\StatusModel(isMigration: true);
        foreach ($jsonData as $row){
            $status = new \App\Entities\Status($row);
            $statusModel->insert($status);
        }
        echo "Seeded Models\n";
    }

    public function seedLocations(){
        echo "Seeding Locations\n";
        $jsonData = $this->readJSON('locations.json');
        $locationModel = new \App\Models\LocationModel(isMigration: true);
        foreach ($jsonData as $row){
            $location = new \App\Entities\Location($row);
            $locationModel->insert($location);
        }
        echo "Seeded Locations\n";
    }

    public function seedFiles(){
        echo "Seeding Files\n";
        $jsonData = $this->readJSON('files.json');
        foreach ($jsonData as $fileData){
            $fileData['filedata'] = $this->readFile($fileData['filedata']);
            $file = new \App\Entities\File($fileData);
            $fileModel = new \App\Models\FileModel(isMigration: true);
            $fileModel->insert($file);
            if (str_starts_with($fileData['name'], 'deleted')){
                $fileModel->delete($file->id);
            }
        }
        echo "Seeded Files\n";
    }

    public function seedEquipments(){
        echo "Seeding Equipments\n";
        $modelModel = new \App\Models\ModelModel(isMigration: true);
        $models = $modelModel->findAll();

        $locationModel = new \App\Models\LocationModel(isMigration: true);
        $locations = $locationModel->findAll();

        $statusModel = new \App\Models\StatusModel(isMigration: true);
        $statuses = $statusModel->findAll();
        
        $jsonData = $this->readJSON('equipments.json');

        $equipmentModel = new \App\Models\EquipmentModel(isMigration: true);
        $first = true;
        
        foreach ($jsonData as $row) {
            $equipment = new \App\Entities\Equipment($row);
            $model = random_choice($models);
            $equipment->model_id = $model->id;
            $location = random_choice($locations);
            $equipment->location_id = $location->id;
            $status = random_choice($statuses);
            $equipment->status_id = $status->id;
            $equipment->model_params = array_fill_keys($model->params, null);
            if (!$first){
                $parentEquipment = $equipmentModel->findAll(limit:1)[0];
                $equipment->parent_id = $parentEquipment->id;
            }
            $equipmentModel->insert($equipment);
            if ($this->probDecision(0.03)){
                $equipmentModel->delete($equipment->id);
            }
            if ($first){
                $first = false;
            }
        }
        echo "Seeded Equipments\n";
    }

    public function seedEquipments2Files(){
        echo "Seeding Equipments2Files\n";
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
        echo "Seeded Equipments2Files\n";
    }

}
