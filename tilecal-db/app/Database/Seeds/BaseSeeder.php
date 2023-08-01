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

    protected function readJSON($filepath){
        $fullpath = ROOTPATH . '/app/Database/Seeds/seed_data/' . $filepath;
        $jsonString = file_get_contents($fullpath);
        $jsonData = json_decode($jsonString, true);
        return $jsonData;
    }
}
