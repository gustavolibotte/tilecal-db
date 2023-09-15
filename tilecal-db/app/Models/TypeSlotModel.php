<?php

namespace App\Models;

class TypeSlotModel extends LogModel
{
    protected $table            = 'type_slots';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\TypeSlot::class;
    protected $allowedFields    = ['type_id', 'type_ids_fit', 'max_count'];

    public function hasSlots(string $typeName){
        $result = $this
            ->select('1')
            ->where(sprintf(
                '(SELECT id FROM equipment_types WHERE name=\'%s\') MEMBER OF(type_ids_fit)',
                $typeName,
            ), escape: false)
            ->first();
        return !empty($result);
    }
}
