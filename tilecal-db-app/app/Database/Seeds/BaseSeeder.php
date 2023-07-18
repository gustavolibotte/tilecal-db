<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BaseSeeder extends Seeder
{
    public function probDecision($prob){
        $deleteEntry = mt_rand(0,999);
        $isDelete = $deleteEntry<($prob*1000);
        return $isDelete;
    }
}
