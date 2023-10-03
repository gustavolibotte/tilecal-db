<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

abstract class BaseEntity extends Entity
{
    public function isDeleted(): bool
    {
        return !empty($this->deleted_at);
    }
}
