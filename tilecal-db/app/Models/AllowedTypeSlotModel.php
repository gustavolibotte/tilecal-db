<?php

namespace App\Models;

class AllowedTypeSlotModel extends LogModel
{
    protected $table            = 'allowed_type_slots';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['slot_id', 'allowed_type_id'];
}
