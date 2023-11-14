<?php

namespace App\Database\Seeds;

use App\Entities\TypeSlot;
use App\Models\TypeSlotModel;

class EquipmentTypeSeeder extends BaseSeeder
{
    public function run()
    {
        $jsonData = $this->readFile('equipment_types.json');
        $equipmentTypeModel = new \App\Models\EquipmentTypeModel();
        foreach ($jsonData as &$row){
            $equipmentType = new \App\Entities\EquipmentType($row);
            $id = $equipmentTypeModel->insert($equipmentType);
            $row['id'] = $id;
        }

        $typeSlotModel = new TypeSlotModel();
        foreach ($jsonData as $row){
            if (empty($row['slots'])){
                continue;
            }
            $slots = $this->getFormatedSlotArray($row);
            foreach ($slots as $slot){
                $typeSlotModel->insert($slot);
            }
        }
    }

    protected function getFormatedSlotArray(array $row): array
    {
        $equipmentTypeModel = new \App\Models\EquipmentTypeModel();
        $nameToId = $equipmentTypeModel->getCustomArray('name', 'id');
        $slots = [];
        foreach($row['slots'] as $relation){
            foreach($relation['allowed_type_ids'] as &$child){
                $child = $nameToId[$child];
            }
            $slots[] = new TypeSlot([
                'parent_type_id' => $row['id'],
                'allowed_type_ids' => $relation['allowed_type_ids'],
                'name' => $relation['name']
            ]);
        }
        return $slots;
    }
}
