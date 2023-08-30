<?php

namespace App\Database\Seeds;

use Tests\Support\Models\EquipmentFabricator;
use CodeIgniter\Test\Fabricator;

class EquipmentSeeder extends BaseSeeder
{
    public function run()
    {
        helper(['random', 'app_array']);
        $numEntries = 100;
        $parentChance = 0.5;
        $fabricatorModel = new EquipmentFabricator(isMigration: true);
        $fabricator = new Fabricator($fabricatorModel);
        $equipments = $fabricator->make($numEntries);
        $fabricatorModel->insertWithLog($equipments);
        $parent = remove_value(0, $equipments);
        foreach ($equipments as $equipment){
            $fabricatorModel->addParent($equipment->id, $parent->id);
            // if (apply_prob($parentChance)){
                
            // }
        }
    }
}
