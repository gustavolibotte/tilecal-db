<?php

namespace App\Models;
use App\Entities\DetectorLocation;

class DetectorLocationModel extends LogModel
{
    protected $table            = 'detector_locations';
    protected $useAutoIncrement = true;
    protected $returnType       = DetectorLocation::class;
    protected $allowedFields    = ['name'];
}
