<?php

namespace App\Models;

class TypeSlotModel extends LogModel
{
    protected $table            = 'type_slots';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\TypeSlot::class;
    protected $allowedFields    = ['parent_type_id', 'children_type_ids', 'max_count'];
}
