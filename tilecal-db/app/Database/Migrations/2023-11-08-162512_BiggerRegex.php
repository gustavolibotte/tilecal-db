<?php

namespace App\Database\Migrations;
use App\Library\BaseMigration;

class BiggerRegex extends BaseMigration
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
