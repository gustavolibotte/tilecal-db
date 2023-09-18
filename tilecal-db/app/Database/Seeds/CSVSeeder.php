<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CSVSeeder extends Seeder
{
    public function run()
    {
        $csvFilepath = $this->readFile('2023-07-07-134759_types.json');
}
