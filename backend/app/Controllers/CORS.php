<?php

namespace App\Controllers;
use CodeIgniter\HTTP\ResponseInterface;

class CORS extends APIController
{
    public function index(): ResponseInterface
    {
        return $this->buildAPIResponse(200);
    }
}
