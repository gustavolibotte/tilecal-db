<?php

namespace App\Controllers;

class WebApp extends APIController
{
    public function index()
    {
        $userAgent = $this->request->getUserAgent();
        if ($userAgent->isBrowser() || $userAgent->isMobile()){
            // Returns React app
            return view('index');
        } else{
            return $this->failBadRequest(message: 'Not an API endpoint');
        }
    }
}
