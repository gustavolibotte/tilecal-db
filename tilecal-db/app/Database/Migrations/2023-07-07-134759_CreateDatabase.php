<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateDatabase extends Migration
{   
    public function addTimestamps(){
        $this->forge->addField([
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
                'null' => true
            ]
        ]);
    }
    public function addId(){
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => TRUE,
                'unsigned' => true
            ]
        ]);
    }

    public function createModels(){
        $this->addId();
        $this->forge->addField([
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => false,
                'unique' => true
            ],
            'in_detector' => [
                'type' => 'BOOLEAN',
                'null' => false,
            ],
            'label' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ],
            'params' => [
                'type' => 'JSON',
                'null' => false
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true
            ]
        ]);
        $this->addTimestamps();
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('models', true);
    }

    public function createLocations(){
        $this->addId();
        $this->forge->addField([
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => false
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => false
            ]
        ]);
        $this->addTimestamps();
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('locations', true);
    }

    public function createStatuses(){
        $this->addId();
        $this->forge->addField([
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => false
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => false
            ]
        ]);
        $this->addTimestamps();
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('statuses', true);
    }

    public function createFiles(){
        $this->addId();
        $this->forge->addField([
            'name' => [
                'mime' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ],
            'filedata' => [
                'type' => 'MEDIUMBLOB',
                'null' => false
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => false
            ]
        ]);
        $this->addTimestamps();
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('files', true);
    }

    public function createEquipments(){
        $this->addId();
        $this->forge->addField([
            'barcode' => [
                'type' => 'CHAR',
                'contraint' => 7,
                'unique' => true,
                'null' => false
            ],
            'model_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ],
            'location_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ],
            'status_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ],
            'parent_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true
            ],
            'serial' => [
                'type' => 'VARCHAR',
                'constraint' => 36, # Number of chars in uuid,
                'null' => true
            ],
            'comments' => [
                'type' => 'TEXT',
                'null' => true
            ]
        ]);
        $this->addTimestamps();
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('model_id', 'models', 'id');
        $this->forge->addForeignKey('parent_id', 'equipments', 'id');
        $this->forge->addForeignKey('location_id', 'locations', 'id');
        $this->forge->addForeignKey('status_id', 'statuses', 'id');
        $this->forge->createTable('equipments', true);
    }

    public function createEquipments2Files(){
        $this->addId();
        $this->forge->addField([
            'file_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ],
            'equipment_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ]
        ]);
        $this->addTimestamps();
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('equipment_id', 'equipments', 'id');
        $this->forge->addForeignKey('file_id', 'files', 'id');
        $this->forge->createTable('equipments_to_files', true);
    }

    public function createHist(){
        $this->addId();
        $this->forge->addField([
            'user' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false
            ],
            'table' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => false
            ],
            'action' => [
                'type' => 'ENUM',
                'constraint' => ['insert', 'update', 'delete'],
                'null' => false
            ],
            'record_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ],
            'changes' => [
                'type' => 'JSON',
                'null' => false
            ],
            'filedata' => [
                'type' => 'MEDIUMBLOB',
                'null' => true
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
                'default' => new RawSql('CURRENT_TIMESTAMP')
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('hist', true);
    }

    public function up()
    {
        $this->createModels();
        $this->createLocations();
        $this->createStatuses();
        $this->createFiles();
        $this->createEquipments();
        $this->createEquipments2Files();
        $this->createHist();
    }

    public function down()
    {
        // Drop tables in reverse order
        $this->forge->dropTable('hist', true);
        $this->forge->dropTable('equipments_to_files', true);
        $this->forge->dropTable('equipments', true);
        $this->forge->dropTable('files', true);
        $this->forge->dropTable('locations', true);
        $this->forge->dropTable('statuses', true);
        $this->forge->dropTable('models', true);

    }
}
