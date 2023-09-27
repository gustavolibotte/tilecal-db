<?php

namespace App\Models;

use App\Entities\ParentChildRelation;

class ParentChildModel extends LogModel
{
    protected $table            = 'parent_child_equips';
    protected $useAutoIncrement = true;
    protected $returnType       = ParentChildRelation::class;
    protected $allowedFields    = ['parent_equip_id', 'child_equip_id', 'slot_id'];
}
