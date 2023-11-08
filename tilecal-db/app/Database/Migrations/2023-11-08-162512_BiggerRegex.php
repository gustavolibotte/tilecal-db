<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BiggerRegex extends Migration
{
    public function up()
    {
        $fields = [
            'id_regex' => [
                'type' => 'VARCHAR',
                'constraint' => 200
            ],
        ];
        $this->forge->modifyColumn('equipment_types', $fields);
    }

    public function down()
    {
        $fields = [
            'id_regex' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
        ];
        $this->forge->modifyColumn('equipment_types', $fields);
    }
}
