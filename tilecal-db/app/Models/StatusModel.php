<?php

namespace App\Models;

class StatusModel extends LogModel
{
    protected $table            = 'statuses';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Status::class;
    protected $allowedFields    = ['name', 'description'];
}
