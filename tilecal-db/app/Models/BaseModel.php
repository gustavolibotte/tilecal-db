<?php

namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    // Custom
    protected bool $isMigration = false;

    public function __construct(bool $isMigration=false)
    {   
        parent::__construct();
        $this->isMigration = $isMigration;
    }
}
