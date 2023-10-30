<?php

namespace App\Controllers;
use App\Library\Readers\CSVFileReader;

class Home extends BaseController
{
    public function index()
    {
        // $filepath = '/usr/local/cernbox/backups/old_database/2023_07_17/cleaned/2023_10_20_09_08_58_cleaned_pmt_202307171723.csv';
        // $csvFile = new CSVFileReader($filepath);
        // $data = [];
        // foreach ($csvFile as $iRow=>$row){
        //     $data[$iRow] = $row;
        // }
        // d($data);
        $welcomeData['pageHeader'] = '';
        $welcomeData['pageTitle'] = 'Welcome to the Tilecal Database';
        return default_view('welcome', $welcomeData);
    }
}
