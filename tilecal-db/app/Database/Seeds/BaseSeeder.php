<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BaseSeeder extends Seeder
{
    protected function readFile($filepath, bool $dataDir=true){
        helper('path');
        if ($dataDir){
            $filepath = joinPaths([ROOTPATH, 'data', $filepath]);
        }
        $filedata = file_get_contents($filepath);
        if (str_ends_with($filepath, '.json')){
            $filedata = json_decode($filedata, true);
        }
        return $filedata;
    }
}
