<?php

namespace App\Database\Seeds;

use Tests\Support\Models\EquipmentFabricator;
use CodeIgniter\Test\Fabricator;

class SeedDatabase extends BaseSeeder
{
    public function run()
    {   
        helper('random');
        // $this->seedFiles();
        $this->seedEquipments(1000);
        // $this->seedEquipments2Files();
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

    public function seedEquipments(int $numEntries){
        echo "Seeding Equipments\n";

        $fabricatorModel = new EquipmentFabricator(isMigration: true);
        $fabricator = new Fabricator($fabricatorModel);
        foreach ($fabricator->make($numEntries) as $equipment){
            $fabricatorModel->insert($equipment);
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
