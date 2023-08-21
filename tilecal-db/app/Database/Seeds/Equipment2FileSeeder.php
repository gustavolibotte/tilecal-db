<?php

namespace App\Database\Seeds;


class Equipment2FileSeeder extends BaseSeeder
{
    public function run()
    {
        helper('random');
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
            $equipments2FileModel->insertWithLog($equipment2File);
        }
    }
}
