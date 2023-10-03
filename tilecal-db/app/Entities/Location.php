<?php

namespace App\Entities;

class Location extends BaseEntity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id' => 'integer',
        'institute_id' => 'integer'
    ];
}
