<?php

namespace App\Models;

class InstituteModel extends LogModel
{
    protected $table            = 'institutes';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Institute::class;
    protected $allowedFields    = ['name', 'mne', 'country'];
}
