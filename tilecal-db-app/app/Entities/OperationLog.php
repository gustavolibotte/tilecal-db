<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class OperationLog extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at'];
    protected $casts   = [
         "changes" => "json",
         "record_id" => "integer"
    ];
}
