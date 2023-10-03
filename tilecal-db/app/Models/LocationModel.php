<?php

namespace App\Models;

class LocationModel extends LogModel
{
    protected $table            = 'locations';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Location::class;
    protected $allowedFields    = ['name', 'institute_id', 'description'];

}
