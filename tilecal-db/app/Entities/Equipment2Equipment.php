<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Equipment2Equipment extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        "id" => "integer",
        "equipment_a_id" => "string",
        "equipment_b_id" => "string",
    ];
}
