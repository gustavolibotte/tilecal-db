<?php

namespace App\Database\Migrations;
use App\Library\BaseMigration;

class FeatureControl extends BaseMigration
{
    public function up()
    {
        $fields = [
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
                'unsigned' => true
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
                'null' => false
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => false
            ] 
        ];
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('features', true);
    }

    public function down()
    {
        $this->forge->dropTable('features', true);
    }
}
