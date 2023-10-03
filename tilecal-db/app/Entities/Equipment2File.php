<?php

namespace App\Entities;

class Equipment2File extends BaseEntity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        "id" => "integer",
        "equipment_id" => "string",
        "file_id" => "integer"
    ];
}
