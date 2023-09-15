<?php

namespace App\Database\Seeds;

use App\Entities\TypeSlot;
use App\Models\TypeSlotModel;

class EquipmentTypeSeeder extends BaseSeeder
{
    public function run()
    {
        service('authenticator')->setMigration();
        $jsonData = $this->readFile('2023-07-07-134759_types.json');
        $equipmentTypeModel = new \App\Models\EquipmentTypeModel();
        foreach ($jsonData as $row){
            $equipmentType = new \App\Entities\EquipmentType($row);
            $equipmentTypeModel->insert($equipmentType);
        }

        $typeSlotModel = new TypeSlotModel();
        foreach ($jsonData as $row){
            if (empty($row['slots'])){
                continue;
            }
            $slots = $this->getFormatedSlotArray($row);
            foreach ($slots as $slot){
                d($slot);
                $typeSlotModel->insert($slot);
            }
        }
    }

    protected function getFormatedSlotArray(array $row): array
    {
        $equipmentTypeModel = new \App\Models\EquipmentTypeModel();
        $nameToId = $equipmentTypeModel->getCustomArray('name', 'id');
        foreach($row['slots'] as $relation){
            foreach($relation['children'] as &$child){
                $child = $nameToId[$child];
            }
            $slots[] = new TypeSlot([
                'parent_type_id' => $row['id'],
                'children_type_ids' => $relation['children'],
                'max_count' => $relation['max_count']
            ]);
        }
        return $slots;
    }
}
