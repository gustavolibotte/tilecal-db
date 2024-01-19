<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class EquipmentType extends APIController
{
    public function get(): ResponseInterface
    {
        try{
            return $this->__get__();
        } catch (Throwable $e){
            return $this->fail($e->getMessage(), 500);
        }
    }

    public function __get__(): ResponseInterface
    {
        $equipmentTypes = model('EquipmentTypeModel')
            ->select('name')
            ->select('label')
            ->where('active', 1)
            ->findAll();
        return $this->buildAPIResponse(200, empty($equipmentTypes)? []: $equipmentTypes);
    }

}
