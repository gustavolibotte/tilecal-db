<?php

namespace App\Controllers;



class Home extends BaseController
{
    public function index()
    {   
        $welcomeData['pageHeader'] = '';
        $welcomeData['pageTitle'] = 'Welcome to the Tilecal Database';
        $model = model('EquipmentModel');
        d($model->opTimestamp);
        return default_view('welcome', $welcomeData);
    }
}
