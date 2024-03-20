<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class AtlasId extends Entity
{
    protected $datamap = [];
    protected $dates   = [];
    protected $casts   = [];

    protected function getSystem(): string
    {
        return substr($this->id, 2, 2);
    }

    protected function getProject(): string
    {
        return substr($this->id, 0, 2);
    }

    protected function getInstitute(): string
    {
        return substr($this->id, 4, 3);
    }

    protected function getSequential(): string
    {
        return substr($this->id, 7, 7);
    }
}
