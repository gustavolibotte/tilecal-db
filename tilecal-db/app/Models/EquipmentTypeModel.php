<?php

namespace App\Models;

class EquipmentTypeModel extends LogModel
{
    protected $table            = 'equipment_types';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\EquipmentType::class;
    protected $allowedFields    = [
        'name',
        'in_detector',
        'system',
        'label',
        'params',
        'description',
        'project',
        'id_regex',
        'active'
    ];
}
