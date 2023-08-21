<?php

namespace App\Database\Seeds;

class InstituteSeeder extends BaseSeeder
{
    public function run()
    {
        $jsonData = $this->readFile('2023-07-07-134759_institutes.json');
        $instituteModel = new \App\Models\InstituteModel(isMigration: true);
        foreach ($jsonData as $row){
            $institute = new \App\Entities\Institute($row);
            $instituteModel->insertWithLog($institute);
        }
    }
}
