<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BaseSeeder extends Seeder
{
    protected function probDecision($prob){
        $deleteEntry = mt_rand(0,999);
        $isDelete = $deleteEntry<($prob*1000);
        return $isDelete;
    }

    protected function readFile($filepath){
        $fullpath = ROOTPATH . 'app/Database/Seeds/seed_data/' . $filepath;
        $filedata = file_get_contents($fullpath);
        return $filedata;
    }

    protected function readJSON($filepath){
        $jsonString = $this->readFile($filepath);
        $jsonData = json_decode($jsonString, true);
        return $jsonData;
    }
}
