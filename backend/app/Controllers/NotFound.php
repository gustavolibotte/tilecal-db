<?php

namespace App\Controllers;

class NotFound extends APIController
{
    public function index()
    {
        $userAgent = $this->request->getUserAgent();
        if ($userAgent->isBrowser() || $userAgent->isMobile()){
            // Returns React app
            return view('index');
        } else{
            return $this->failNotFound();
        }
    }
}
