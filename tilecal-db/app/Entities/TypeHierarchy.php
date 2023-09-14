<?php

namespace App\Entities;

class TypeHierarchy extends BaseEntity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'parent_type_id' => 'integer',
        'children_type_ids' => 'json-array'
    ];
}
