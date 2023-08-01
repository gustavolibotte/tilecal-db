<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Model extends Entity
{
    protected $datamap = [
        'inDetector' => 'in_detector',
        "createdAt" => "created_at",
        "updatedAt" => "updated_at",
        "deletedAt" => "deleted_at"
    ];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id' => 'integer',
        'in_detector' => 'bool',
        'params' => 'json'
    ];
}
