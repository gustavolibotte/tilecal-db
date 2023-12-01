<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InstituteNameExtension extends Migration
{
    public function up()
    {
        $fields = [
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 300
            ],
        ];
        $this->forge->modifyColumn('institutes', $fields);
    }

    public function down()
    {
        $fields = [
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
        ];
        $this->forge->modifyColumn('institutes', $fields);
    }
}
