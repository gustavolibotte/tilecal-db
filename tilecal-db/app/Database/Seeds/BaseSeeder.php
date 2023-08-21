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

    protected function readFile($filename){
        helper('path');
        $fullpath = joinPaths([ROOTPATH, 'data', $filename]);
        $filedata = file_get_contents($fullpath);
        if (str_ends_with($fullpath, '.json')){
            $filedata = json_decode($filedata, true);
        }
        return $filedata;
    }
}
