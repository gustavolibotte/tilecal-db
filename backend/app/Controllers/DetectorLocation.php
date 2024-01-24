<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class DetectorLocation extends APIController
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
        $data = model('DetectorLocationModel')
            ->select('name')
            ->findAll();
        return $this->buildAPIResponse(200, empty($data)? []: $data);
    }
}
