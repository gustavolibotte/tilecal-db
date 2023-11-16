<?php

namespace App\Database\Seeds;

class FeatureSeeder extends BaseSeeder
{
    public function run()
    {
        $jsonData = $this->readFile('features.json');
        $featureModel = model('FeatureModel');
        foreach ($jsonData as $row){
            $feature = new \App\Entities\Feature($row);
            $featureModel->insert($feature);
        }
    }
}
