<?php

namespace App\Models;

class Equipment2FileModel extends LogModel
{
    protected $table            = 'equipments_to_files';
    protected $useAutoIncrement = true;
    protected $returnType       =  \App\Entities\Equipment2File::class;
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['equipment_id', 'file_id'];
}
