<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

abstract class APIController extends BaseController
{
    use ResponseTrait;

    abstract protected function __get__(): ResponseInterface;

    public function get(): ResponseInterface
    {
        $msg = $this->checkAjaxRequest();
        if (!empty($msg)){
            return $this->fail($msg, 400);
        }
        try{
            return $this->__get__();
        } catch (Exception $e){
            return $this->fail($e->getMessage(), 500);
        }
    }
}
