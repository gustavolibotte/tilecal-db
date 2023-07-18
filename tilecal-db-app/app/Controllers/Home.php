<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {   
        $headerData['pageTitle'] = 'Tilecal Database';
        return view('templates/header', $headerData)
               .view('welcome')
               .view('templates/footer');
    }
}
