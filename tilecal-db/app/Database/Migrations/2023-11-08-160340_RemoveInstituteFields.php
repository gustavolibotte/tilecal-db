<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveInstituteFields extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('institutes', ['num', 'place']);
        $this->forge->dropColumn('institutes_hist', ['num', 'place']);
    }

    public function down()
    {
        $fields = [
        'num' => [
            'type' => 'CHAR',
            'constraint' => 3,
            'null' => true,
            'unique' => false
        ],
        'place' => [
            'type' => 'VARCHAR',
            'constraint' => 50,
            'null' => false,
        ]];
        $this->forge->addColumn('institutes', $fields);
        $this->forge->addColumn('institutes_hist', $fields);
    }
}
