<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Equipment extends Entity
{
    protected $datamap = [
        "modelName" => "model_name",
        "modelLabel" => "model_label"
    ];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        "id" => "integer",
        "parent_id" => "?integer",  // Allows null values
        "model_params" => "?json"
    ];
}
