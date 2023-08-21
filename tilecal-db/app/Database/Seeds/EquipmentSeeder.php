<?php

namespace App\Database\Seeds;

use Tests\Support\Models\EquipmentFabricator;
use CodeIgniter\Test\Fabricator;

class EquipmentSeeder extends BaseSeeder
{
    public function run()
    {
        $numEntries = 1000;
        $fabricatorModel = new EquipmentFabricator(isMigration: true);
        $fabricator = new Fabricator($fabricatorModel);
        $equipments = $fabricator->make($numEntries);
        $fabricatorModel->insertWithLog($equipments);
    }
}
