<?php

namespace App\Controllers;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class User extends APIController
{
    protected array $returnFields = [
        'username',
        'roles'
    ];

    public function get(): ResponseInterface
    {
        try{
            return $this->__get__();
        } catch (Throwable $e){
            return $this->failInternalError($e);
        }
    }

    protected function __get__()
    {
        return $this->respondQueryResults(
            \Config\Services::user()->asArray()
        );
    }
}
