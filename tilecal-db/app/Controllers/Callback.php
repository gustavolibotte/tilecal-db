<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Callback extends BaseController
{
    public function callback()
    {
        $session = \Config\Services::session();
        $lastUrl = $session->get('lastURL');
        if (empty($lastUrl) || str_ends_with($lastUrl, 'callback')){
            return redirect()->route('home');
        } else{
            return redirect()->to($lastUrl);
        }
    }
}
