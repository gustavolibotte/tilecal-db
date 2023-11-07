<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $feature = model('FeatureModel')->findName('home');
        if (!$feature->isActive()){
            return $this->serviceUnavailable();
        }
        $welcomeData['pageHeader'] = '';
        $welcomeData['pageTitle'] = 'Welcome to the Tilecal Database';
        return default_view('welcome', $welcomeData);
    }
}
