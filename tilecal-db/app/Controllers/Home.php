<?php

namespace App\Controllers;



class Home extends BaseController
{
    public function index()
    {   
        $welcomeData['pageTitle'] = 'Welcome to the Tilecal Database';
        return view('welcome', $welcomeData);
    }
}
