<?php

namespace App\Entities;

class TypeSlot extends BaseEntity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'parent_type_id' => 'integer',
        'type_ids_fit' => 'json-array'
    ];
}
