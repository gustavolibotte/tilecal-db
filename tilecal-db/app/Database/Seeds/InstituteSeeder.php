<?php

namespace App\Database\Seeds;

class InstituteSeeder extends BaseSeeder
{
    public function run()
    {
        $jsonData = $this->readFile('institutes.json');
        $instituteModel = new \App\Models\InstituteModel();
        foreach ($jsonData as $row){
            $institute = new \App\Entities\Institute($row);
            $instituteModel->insert($institute);
        }
    }
}
