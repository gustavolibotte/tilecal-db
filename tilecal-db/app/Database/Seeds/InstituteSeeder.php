<?php

namespace App\Database\Seeds;

class InstituteSeeder extends BaseSeeder
{
    public function run()
    {
        service('authenticator')->setMigration();
        $jsonData = $this->readFile('2023-07-07-134759_institutes.json');
        $instituteModel = new \App\Models\InstituteModel();
        foreach ($jsonData as $row){
            $institute = new \App\Entities\Institute($row);
            $instituteModel->insert($institute);
        }
        $instituteModel->delete(1);
        $instituteModel->restore(1);
    }
}
