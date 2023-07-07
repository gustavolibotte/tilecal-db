<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FillDatabase extends Seeder
{
    public function run()
    {   
        echo "Model Seeding\n";
        $this->call('ModelSeeder');
        echo "Equipment Seeding\n";
        $this->call('EquipmentSeeder');
        echo "File Seeding\n";
        $this->call('FileSeeder');
        echo "Equipment2File Seeding\n";
        $this->call('Equipment2FileSeeder');
        echo "Finished\n";
    }
}
