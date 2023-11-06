<?php

namespace App\Controllers;
use App\Library\Readers\CSVFileReader;
use Config\Services;

class Home extends BaseController
{
    public function index()
    {
        $welcomeData['pageHeader'] = '';
        $welcomeData['pageTitle'] = 'Welcome to the Tilecal Database';
        return default_view('welcome', $welcomeData);
    }
}
