<?php

namespace App\Database\Seeds;


class StatusSeeder extends BaseSeeder
{
    public function run()
    {
        service('authenticator')->setMigration();
        $jsonData = $this->readFile('statuses.json');
        $statusModel = new \App\Models\StatusModel();
        foreach ($jsonData as $row){
            $status = new \App\Entities\Status($row);
            $statusModel->insert($status);
        }
    }
}
