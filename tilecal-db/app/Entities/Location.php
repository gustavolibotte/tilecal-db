<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Location extends Entity
{
    protected $datamap = [
        "createdAt" => "created_at",
        "updatedAt" => "updated_at",
        "deletedAt" => "deleted_at"
    ];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
}
