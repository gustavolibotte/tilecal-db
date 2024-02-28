<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Library\DataFrame;
use Config\Database;
use CodeIgniter\Database\BaseConnection;
use ValueError;

class BaseSeeder extends Seeder
{
    public $db;
    public array $allowedEnvs = [
        'development',
        'local'
    ];

    public function __construct(Database $config, ?BaseConnection $db = null)
    {
        parent::__construct($config, $db);
        \Config\Services::user()->setSystemUser();
    }

    protected function readFile($filepath, bool $dataDir=true)
    {
        helper(['path']);
        if ($dataDir){
            $filepath = joinPaths([ROOTPATH, 'data', $filepath]);
        }
        $filedata = file_get_contents($filepath);
        if (str_ends_with($filepath, '.json')){
            $filedata = json_decode($filedata, true);
        } elseif (str_ends_with($filepath, '.csv')){
            $filedata = DataFrame::fromCSV($filepath);
        }
        return $filedata;
    }
}
