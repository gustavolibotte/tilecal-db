<?php

namespace App\Database\Seeds;

class CSVSeeder extends BaseSeeder
{
    public function run()
    {
        $csvFilepath = $this->readFile('2023-07-07-134759_types.json');
    }
}
