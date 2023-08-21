<?php

namespace App\Database\Seeds;

class EquipmentTypeSeeder extends BaseSeeder
{
    public function run()
    {
        $jsonData = $this->readFile('2023-07-07-134759_types.json');
        $equipmentTypeModel = new \App\Models\EquipmentTypeModel(isMigration: true);
        foreach ($jsonData as $row){
            $equipmentType = new \App\Entities\EquipmentType($row);
            $equipmentTypeModel->insertWithLog($equipmentType);
        }
    }
}
