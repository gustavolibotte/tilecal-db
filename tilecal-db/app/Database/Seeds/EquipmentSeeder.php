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
        $deleteChance = 0.05;
        $fabricatorModel = new EquipmentFabricator();
        $fabricator = new Fabricator($fabricatorModel);
        $equipments = $fabricator->make($numEntries);
        $parent = remove_value(0, $equipments);
        $fabricatorModel->insert($parent);
        foreach ($equipments as $equipment){
            $equipment->parent_id = $parent->id;
            $fabricatorModel->insert($equipment);
            if(apply_prob($deleteChance)){
                $fabricatorModel->delete($equipment->id);
                $fabricatorModel->restore($equipment->id);
            }
        }
    }
}
