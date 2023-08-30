<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BaseSeeder extends Seeder
{
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
