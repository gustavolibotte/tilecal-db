<?php

namespace App\Database\Seeds;

use App\Models\Equipment2FileModel;
use App\Models\EquipmentModel;
use App\Models\FileModel;

class Equipment2FileSeeder extends BaseSeeder
{
    public function run()
    {   
        $equipmentModel = new EquipmentModel(migration: true);
        $equipments = $equipmentModel->getEquipment();
        $fileModel = new FileModel(migration: true);
        $files = $fileModel->getFile();
        $equipment2FileModel = new Equipment2FileModel(migration: true);
        foreach ($equipments as $equipment){
            if ($this->probDecision(1)){
                $fileIdx = array_rand($files);
                $fileBind = $files[$fileIdx];
                $data = [
                    "equipment_id" => $equipment->id,
                    "file_id" => $fileBind->id
                ];
                $equipment2FileModel->insert($data);
            }
        }
    }


}
