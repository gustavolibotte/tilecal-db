<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Equipment extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        "model_params" => "json-array",
        "location_id" => "integer",
        "status_id" => "integer",
        "model_id" => "integer"
    ];
}
