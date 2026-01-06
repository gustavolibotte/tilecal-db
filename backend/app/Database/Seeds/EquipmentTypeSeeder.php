<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Entities\EquipmentType;
use App\Entities\TypeSlot;

class EquipmentTypeSeeder extends BaseSeeder
{
    public function run()
    {
        $jsonData = $this->readFile('equipment_types.json');
        
        $equipmentTypeModel = model('EquipmentTypeModel');
        $typeSlotModel      = model('TypeSlotModel');
        $allowedTypeSlotModel = model('AllowedTypeSlotModel');

        foreach ($jsonData as $row) {
            $slotsData = $row['slots'] ?? [];
            unset($row['slots']);

            $existing = $equipmentTypeModel->where('name', $row['name'])->first();

            if ($existing) {
                $row['id'] = $existing->id;
            }

            $equipmentType = new EquipmentType($row);
            
            $equipmentTypeModel->save($equipmentType);

            $typeId = $existing ? $existing->id : $equipmentTypeModel->getInsertID();
            
            if (!$typeId && $existing) {
                $typeId = $existing->id;
            }

            if (!empty($slotsData) && $typeId) {
                foreach ($slotsData as $slotDef) {
                    $slotName = $slotDef['name'];

                    $existingSlot = $typeSlotModel->where('name', $slotName)
                                                  ->where('parent_type_id', $typeId)
                                                  ->first();

                    if ($existingSlot) {
                        $slotId = $existingSlot->id;
                    } else {
                        $newSlot = new TypeSlot([
                            'name' => $slotName,
                            'parent_type_id' => $typeId
                        ]);
                        
                        $typeSlotModel->insert($newSlot);
                        $slotId = $typeSlotModel->getInsertID();
                    }

                    $allowedTypeNames = $slotDef['allowed_type_ids'] ?? [];

                    foreach ($allowedTypeNames as $childTypeName) {
                        $childType = $equipmentTypeModel->where('name', $childTypeName)->first();

                        if ($childType) {
                            $existsAllowed = $allowedTypeSlotModel->where('slot_id', $slotId)
                                                                  ->where('allowed_type_id', $childType->id)
                                                                  ->first();

                            if (!$existsAllowed) {
                                $allowedTypeSlotModel->insert([
                                    'slot_id' => $slotId,
                                    'allowed_type_id' => $childType->id
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }
}