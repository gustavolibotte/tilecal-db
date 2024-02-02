<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Institute extends APIController
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
        $data = model('InstituteModel')
            ->select('name')
            ->select('mne')
            ->select('country')
            ->findAll();
        return $this->buildAPIResponse(200, empty($data)? []: $data);
    }
}
