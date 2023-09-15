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
                'constraint' => 6,
                'null' => false
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'constraint' => 6,
                'null' => false
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
                'constraint' => 6,
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

    public function createEquipmentTypes(){
        echo "Creating Equipment Types\n";

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
            'system' => [
                'type' => 'ENUM',
                'constraint' => ['LB', 'LE', 'LO', 'LL', 'LC'],
                'null' => false
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
        $this->forge->addUniqueKey('name', 'equipment_types_unique_name');
        $this->forge->createTable('equipment_types', true);

        echo "Created Equipment Types\n";
    }

    public function createInstitutes(){
        echo "Creating Institutes\n";
        $this->addId();
        $this->forge->addField([
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false
            ],
            'mne' => [
                'type' => 'CHAR',
                'constraint' => 3,
                'null' => false,
                'unique' => true
            ],
            'num' => [
                'type' => 'CHAR',
                'constraint' => 3,
                'null' => false,
                'unique' => true
            ],
            'place' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'country' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => false,
            ]
        ]);
        $this->addTimestamps();
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('mne', 'intitutes_unique_mne');
        $this->forge->addUniqueKey('num', 'institutes_unique_num');
        $this->forge->createTable('institutes', true);

        echo "Created Institutes\n";
    }

    public function createLocations(){
        echo "Creating Locations\n";

        $this->addId();
        $this->forge->addField([
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false
            ],
            'institute_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 280,
                'null' => true
            ]
        ]);
        $this->addTimestamps();
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('institute_id', 'institutes', 'id');
        $this->forge->createTable('locations', true);

        echo "Created Locations\n";
    }

    public function createStatuses(){
        echo "Creating Statuses\n";

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

        echo "Created Statuses\n";
    }

    public function createFiles(){
        echo "Creating Files\n";
        $this->addId();
        $this->forge->addField([
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false
            ],
            'mime' => [
                'type' => 'VARCHAR',
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
        echo "Created Files\n";
    }

    public function createEquipments(){

        echo "Creating Equipments\n";
        $this->forge->addField([
            'id' => [
                'type' => 'CHAR',
                'constraint' => 14,
            ],
            'type_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ],
            'owner_id' => [
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
            'serial' => [
                'type' => 'VARCHAR',
                'constraint' => 36, # Number of chars in uuid,
                'null' => true
            ],
            'type_params' => [
                'type' => 'JSON',
                'null' => false
            ],
            'comments' => [
                'type' => 'TEXT',
                'null' => true
            ]
        ]);
        $this->addTimestamps();
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('type_id', 'equipment_types', 'id');
        $this->forge->addForeignKey('owner_id', 'institutes', 'id');
        $this->forge->addForeignKey('location_id', 'locations', 'id');
        $this->forge->addForeignKey('status_id', 'statuses', 'id');
        $this->forge->createTable('equipments', true);
        echo "Created Equipments\n";

    }

    public function createEquipments2Files(){
        echo "Creating Equipments to Files\n";
        $this->addId();
        $this->forge->addField([
            'file_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ],
            'equipment_id' => [
                'type' => 'CHAR',
                'constraint' => 14,
                'null' => false
            ]
        ]);
        $this->addTimestamps();
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('equipment_id', 'equipments', 'id');
        $this->forge->addForeignKey('file_id', 'files', 'id');
        $this->forge->addUniqueKey(['file_id', 'equipment_id'], 'unique_file_equipment');
        $this->forge->createTable('equipments_to_files', true);
        echo "Created Equipments to Files\n";

    }

    public function createHist(){
        echo "Creating Hist\n";
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
                'constraint' => ['insert', 'update', 'delete', 'restore'],
                'null' => false
            ],
            'record_id' => [
                'type' => 'VARCHAR',
                'constraint' => 14,
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
                'constraint' => 6
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('hist', true);
        echo "Created Hist\n";
    }

    public function Equipments2Equipments()
    {
        echo "Creating Equipments to Equipments\n";
        $this->addId();
        $this->forge->addField([
            'equipment_a_id' => [
                'type' => 'CHAR',
                'constraint' => 14,
                'null' => false
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false
            ],
            'equipment_b_id' => [
                'type' => 'CHAR',
                'constraint' => 14,
                'null' => false
            ],
        ]);
        $this->addTimestamps();
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('equipment_a_id', 'equipments', 'id');
        $this->forge->addForeignKey('equipment_b_id', 'equipments', 'id');
        $this->forge->addUniqueKey(['equipment_a_id', 'equipment_b_id'], 'unique_equipment_equipment');
        $this->forge->createTable('equipments_to_equipments', true);
        echo "Created Equipments to Equipments\n";
    }

    public function createTypeSlots()
    {
        echo "Creating Type Slots\n";
        $this->addId();
        $this->forge->addField([
            'parent_type_id' => [
                'type' => 'INT',
                'unsigned'=> true,
                'null' => false
            ],
            'children_type_ids' => [
                'type' => 'JSON',
                'null' => false
            ],
            'max_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ],
        ]);
        $this->addTimestamps();
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('parent_type_id', 'equipment_types', 'id');
        $this->forge->createTable('type_slots', true);
        echo "Created Type Slots\n";
    }

    public function up()
    {
        $this->createHist();
        $this->createEquipmentTypes();
        $this->createTypeSlots();
        $this->createInstitutes();
        $this->createLocations();
        $this->createStatuses();
        $this->createFiles();
        $this->createEquipments();
        $this->Equipments2Equipments();
        $this->createEquipments2Files();
    }

    public function down()
    {
        // Drop tables in reverse order
        $this->forge->dropTable('hist', true);
        $this->forge->dropTable('equipments_to_equipments', true);
        $this->forge->dropTable('equipments_to_files', true);
        $this->forge->dropTable('equipments', true);
        $this->forge->dropTable('files', true);
        $this->forge->dropTable('institutes', true);
        $this->forge->dropTable('locations', true);
        $this->forge->dropTable('statuses', true);
        $this->forge->dropTable('types_slots', true);
        $this->forge->dropTable('equipment_types', true);
    }
}
