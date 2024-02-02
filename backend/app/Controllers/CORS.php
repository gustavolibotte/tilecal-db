<?php

namespace App\Controllers;

class CORS extends APIController
{
    public function index()
    {
        return $this->respond([], 200);
    }
}
