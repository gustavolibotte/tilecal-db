<?php

namespace App\Controllers;
use App\Library\Forms\EquipmentForm;
use App\Library\Forms\PmtForm;

class Home extends BaseController
{
    public function index()
    {
        $welcomeData['pageHeader'] = '';
        $welcomeData['pageTitle'] = 'Welcome to the Tilecal Database';
        return default_view('welcome', $welcomeData);
    }
}
