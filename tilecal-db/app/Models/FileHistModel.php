<?php

namespace App\Models;
use App\Entities\File;

class FileHistModel extends BaseModel
{
    protected $table  = 'files_hist';
    protected $useAutoIncrement = true;
    protected $returnType = File::class;
    protected $protectFields = false;
    protected $modelAllowedFields;
}
