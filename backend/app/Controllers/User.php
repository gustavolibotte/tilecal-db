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
            return $this->throwError(500, $e);
        }
    }

    protected function __get__()
    {
        $authenticator = \Config\Services::authenticator();
        $user = $authenticator->getUser();
        $data = [];
        foreach ($this->returnFields as $fieldName){
            $data[$fieldName] = $user->$fieldName;
        }
        return $this->buildAPIResponse(200, $data, '');
    }
}
