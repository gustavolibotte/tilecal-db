<?php

namespace App\Database\Seeds;

class SeedDatabase extends BaseSeeder
{
    public function run()
    {   
        helper('random');
        $this->seedFiles();
        $this->seedEquipments();
        $this->seedEquipments2Files();
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

        $instituteModel = new \App\Models\InstituteModel(isMigration: true);
        $institutes = $instituteModel->findAll();

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
            $institute = random_choice($institutes);
            $equipment->owner_id = $institute->id;

            $equipment->id = sprintf(
                '20%s%s%s',
                $model->system, $institute->mne, $equipment->id
            );

            $equipment->model_params = array_fill_keys($model->params, null);
            if (!$first){
                $equipment->parent_id = $firstEquipment->id;
            }
            $equipmentModel->insert($equipment);
            if ($this->probDecision(0.03)){
                $equipmentModel->delete($equipment->id);
            }
            if ($first){
                $firstEquipment = $equipment;
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
