<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class User extends Entity
{
    protected $datamap = [];
    protected $dates   = ['authTime'];
    protected $casts   = [];

    public function canAlter(){
        return(
            in_array('editor', $this->roles) ||
            in_array('admin', $this->roles)
        );
    }
}
