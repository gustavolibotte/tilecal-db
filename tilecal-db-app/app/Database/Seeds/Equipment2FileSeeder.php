<?php

namespace App\Database\Seeds;

use App\Models\Equipment2FileModel;
use App\Models\EquipmentModel;
use App\Models\FileModel;

class Equipment2FileSeeder extends BaseSeeder
{
    public function run()
    {   
        $equipmentModel = new EquipmentModel();
        $equipments = $equipmentModel->getEquipment();
        $fileModel = new FileModel();
        $files = $fileModel->getFile();
        $equipment2FileModel = new Equipment2FileModel();
        foreach ($equipments as $equipment){
            if ($this->probDecision(0.1)){
                $fileIdx = array_rand($files);
                $fileBind = $files[$fileIdx];
                $data = [
                    "equipment_id" => $equipment->id,
                    "file_id" => $fileBind->id
                ];
                $equipment2FileModel->insert($data);
            }
        }

        // $seedDataPath = '/root/tilecal-db/app/Database/Seeds/seed_data/equipments2files.csv';
        // $csv = array_map("str_getcsv", file($seedDataPath, FILE_SKIP_EMPTY_LINES));
        // $keys = array_shift($csv);
        // $equipment2FileModel = new Equipment2FileModel();
        // foreach ($csv as $row) {
        //     $data = array_combine($keys, $row);
        //     $equipment2FileModel->insert($data);
        // }
    }


}
