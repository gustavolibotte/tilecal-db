<?php

namespace App\Database\Seeds;

class DetectorLocationSeeder extends BaseSeeder
{
    public function run()
    {
        $jsonData = $this->readFile('tile_modules.json');
        $detectorLocationModel = model('DetectorLocationModel');
        foreach ($jsonData as $row){
            $detectorLocation = new \App\Entities\DetectorLocation($row);
            $detectorLocationModel->insert($detectorLocation);
        }
    }
}
