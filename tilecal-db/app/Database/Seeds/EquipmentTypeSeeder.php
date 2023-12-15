<?php

namespace App\Database\Seeds;

use App\Entities\TypeSlot;

class EquipmentTypeSeeder extends BaseSeeder
{
    public function run()
    {
        $jsonData = $this->readFile('equipment_types.json');
        $equipmentTypeModel = model('EquipmentTypeModel');
        foreach ($jsonData as $row){
            $equipmentType = new \App\Entities\EquipmentType($row);
            $equipmentTypeModel->insert($equipmentType);        }
    }
}
