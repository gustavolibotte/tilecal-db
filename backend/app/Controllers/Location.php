<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Location extends APIController
{
    public function get(): ResponseInterface
    {
        try{
            return $this->__get__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    public function __get__(): ResponseInterface
    {
        $data = model('LocationModel')
            ->select('name')
            ->select('description')
            ->findAll();
        return $this->respondQueryResults($data);
    }
}
