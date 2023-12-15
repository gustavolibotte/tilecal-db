<?php

namespace App\Entities;

class EquipmentType extends BaseEntity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id' => 'integer',
        'in_detector' => 'bool',
        'params' => 'json-array',
        'slots' => '?json'
    ];
}
