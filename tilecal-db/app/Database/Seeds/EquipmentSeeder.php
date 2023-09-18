<?php

namespace App\Database\Seeds;

use Tests\Support\Models\EquipmentFabricator;
use CodeIgniter\Test\Fabricator;

class EquipmentSeeder extends BaseSeeder
{
    public function run()
    {
        helper(['random', 'app_array']);
        service('authenticator')->setMigration();
        $numEntries = 100;
        $spareChance = 0.1;
        $deleteChance = 0.03;
        $fabricatorModel = new EquipmentFabricator($spareChance);
        $fabricatorModel->setAsArray();
        $fabricator = new Fabricator($fabricatorModel);
        $equipments = $fabricator->make($numEntries);
        $equipments = array_merge(...$equipments);
        $fabricatorModel->setAsObject();
        foreach ($equipments as $equipment){
            $fabricatorModel->insert($equipment);
            if(apply_prob($deleteChance)){
                $fabricatorModel->delete($equipment->id);
            }
        }
    }
}
