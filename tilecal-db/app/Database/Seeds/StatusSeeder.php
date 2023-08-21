<?php

namespace App\Database\Seeds;


class StatusSeeder extends BaseSeeder
{
    public function run()
    {
        $jsonData = $this->readFile('2023-07-07-134759_statuses.json');
        $statusModel = new \App\Models\StatusModel(isMigration: true);
        foreach ($jsonData as $row){
            $status = new \App\Entities\Status($row);
            $statusModel->insertWithLog($status);
        }
    }
}
