<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TypeSlotsNameExtension extends Migration
{
    public function up()
    {
        $fields = [
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
        ];
        $this->forge->modifyColumn('type_slots', $fields); 
        $this->forge->modifyColumn('type_slots_hist', $fields); 

        //Fixing last institute name extension

        $fields = [
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 300
            ],
        ];
        $this->forge->modifyColumn('institutes_hist', $fields);
    }

    public function down()
    {
        $fields = [
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 30
            ],
        ];
        $this->forge->modifyColumn('type_slots', $fields);
        $this->forge->modifyColumn('type_slots_hist', $fields); 

        // Fixing last institute name extention

        $fields = [
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
        ];
        $this->forge->modifyColumn('institutes_hist', $fields);
    }
}
