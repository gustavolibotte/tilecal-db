<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateDatabase extends Migration
{   
    public array $defaultIdField = [
        'type' => 'INT',
        'auto_increment' => true,
        'unsigned' => true
    ];

    public array $defaultTimestampFields = [
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
    ];

    public array $equipmentIdFieldConfig = [
        'type'=> 'CHAR',
        'constraint' => 7,
        'null' => false
    ];
    
    protected function addTimestamps(array $tableFields): array
    {
        return array_merge($tableFields, $this->defaultTimestampFields);
    }

    protected function getHistFields(array $tableFields): array
    {
        echo "Create Hist\n";
        helper('app_array');
        $recordId = remove_value('id', $tableFields);
        remove_value('auto_increment', $recordId, false);
        $baseFields = [
            'id' => $this->defaultIdField,
            'user' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false
            ],
            'action' => [
                'type' => 'ENUM',
                'constraint' => ['insert', 'update', 'delete', 'restore'],
                'null' => false
            ],
            'record_id' => $recordId,
        ];
        remove_values(['updated_at', 'deleted_at'], $tableFields);
        $fields = array_merge($baseFields, $tableFields);
        return $fields;
    }

    public function createEquipmentTypes(){
        echo "Creating Equipment Types\n";
        $fields = [
            'id' => $this->defaultIdField,
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
            'project' => [
                'type' => 'CHAR',
                'constraint' => 2,
                'null' => false
            ],
            'system' => [
                'type' => 'ENUM',
                'constraint' => ['LB', 'LE', 'LO', 'LL', 'LC'],
                'null' => false
            ],
            'sequential_start' => [
                'type' => 'VARCHAR',
                'constraint' => 7
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
        ];
        $fields = $this->addTimestamps($fields);
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('name', 'equipment_types_unique_name');
        $this->forge->createTable('equipment_types', true);

        $this->forge->addField($this->getHistFields($fields));
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('equipment_types_hist', true);

        echo "Created Equipment Types\n";
    }

    public function createInstitutes(){
        echo "Creating Institutes\n";
        $fields = [
            'id' => $this->defaultIdField,
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
        ];
        $fields = $this->addTimestamps($fields);
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('mne', 'intitutes_unique_mne');
        $this->forge->addUniqueKey('num', 'institutes_unique_num');
        $this->forge->createTable('institutes', true);

        $this->forge->addField($this->getHistFields($fields));
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('institutes_hist', true);

        echo "Created Institutes\n";
    }

    public function createLocations(){
        echo "Creating Locations\n";

        $fields = [
            'id' => $this->defaultIdField,
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
        ];
        $fields = $this->addTimestamps($fields);
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('institute_id', 'institutes', 'id');
        $this->forge->createTable('locations', true);

        $this->forge->addField($this->getHistFields($fields));
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('institute_id', 'institutes', 'id');
        $this->forge->createTable('locations_hist', true);

        echo "Created Locations\n";
    }

    public function createStatuses(){
        echo "Creating Statuses\n";

        $fields = [
            'id' => $this->defaultIdField,
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => false
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => false
            ]
        ];
        $fields = $this->addTimestamps($fields);
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('statuses', true);

        $this->forge->addField($this->getHistFields($fields));
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('statuses_hist', true);

        echo "Created Statuses\n";
    }

    public function createFiles(){
        echo "Creating Files\n";
        $fields = [
            'id' => $this->defaultIdField,
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false
            ],
            'path' => [
                'type' => 'VARCHAR',
                'constraint' => 300,
                'null' => false
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => false
            ]
        ];
        $fields = $this->addTimestamps($fields);
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('files', true);

        $this->forge->addField($this->getHistFields($fields));
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('files_hist', true);

        echo "Created Files\n";
    }

    public function createEquipments(){

        echo "Creating Equipments\n";
        $fields = [
            'id' => $this->equipmentIdFieldConfig,
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
        ];
        $fields = $this->addTimestamps($fields);
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('type_id', 'equipment_types', 'id');
        $this->forge->addForeignKey('owner_id', 'institutes', 'id');
        $this->forge->addForeignKey('location_id', 'locations', 'id');
        $this->forge->addForeignKey('status_id', 'statuses', 'id');
        $this->forge->createTable('equipments', true);

        $this->forge->addField($this->getHistFields($fields));
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('type_id', 'equipment_types', 'id');
        $this->forge->addForeignKey('owner_id', 'institutes', 'id');
        $this->forge->addForeignKey('location_id', 'locations', 'id');
        $this->forge->addForeignKey('status_id', 'statuses', 'id');
        $this->forge->createTable('equipments_hist', true);

        echo "Created Equipments\n";

    }

    public function createEquipments2Files(){
        echo "Creating Equipments to Files\n";

        $fields = [
            'id' => $this->defaultIdField,
            'file_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ],
            'equipment_id' => $this->equipmentIdFieldConfig
        ];
        $fields = $this->addTimestamps($fields);
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('equipment_id', 'equipments', 'id');
        $this->forge->addForeignKey('file_id', 'files', 'id');
        $this->forge->addUniqueKey(['file_id', 'equipment_id'], 'unique_file_equipment');
        $this->forge->createTable('equipments_to_files', true);

        $this->forge->addField($this->getHistFields($fields));
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('equipment_id', 'equipments', 'id');
        $this->forge->addForeignKey('file_id', 'files', 'id');
        $this->forge->createTable('equipments_to_files_hist', true);

        echo "Created Equipments to Files\n";

    }

    public function ParentChildEquips()
    {
        echo "Creating Parent Child Equips\n";
        $fields = [
            'id' => $this->defaultIdField,
            'parent_equip_id' => [
                'type' => 'CHAR',
                'constraint' => 14,
                'null' => false
            ],
            'child_equip_id' => [
                'type' => 'CHAR',
                'constraint' => 14,
                'null' => false
            ],
            'slot_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false
            ]
        ];
        $fields = $this->addTimestamps($fields);
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('parent_equip_id', 'equipments', 'id');
        $this->forge->addForeignKey('child_equip_id', 'equipments', 'id');
        $this->forge->addForeignKey('slot_id', 'type_slots', 'id');
        $this->forge->addUniqueKey('child_equip_id', 'unique_child_equip_id');
        $this->forge->createTable('parent_child_equips', true);

        $this->forge->addField($this->getHistFields($fields));
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('parent_equip_id', 'equipments', 'id');
        $this->forge->addForeignKey('child_equip_id', 'equipments', 'id');
        $this->forge->addForeignKey('slot_id', 'type_slots', 'id');
        $this->forge->createTable('parent_child_equips_hist', true);

        echo "Created Parent Child Equips\n";
    }

    public function createTypeSlots()
    {
        echo "Creating Type Slots\n";
        $fields = [
            'id' => $this->defaultIdField,
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => false
            ],
            'parent_type_id' => [
                'type' => 'INT',
                'unsigned'=> true,
                'null' => false
            ]
        ];
        $fields = $this->addTimestamps($fields);
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('parent_type_id', 'equipment_types', 'id');
        $this->forge->addUniqueKey(['name', 'parent_type_id']);
        $this->forge->createTable('type_slots', true);

        $this->forge->addField($this->getHistFields($fields));
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('parent_type_id', 'equipment_types', 'id');
        $this->forge->addUniqueKey(['name', 'parent_type_id']);
        $this->forge->createTable('type_slots_hist', true);

        echo "Created Type Slots\n";
    }

    public function createAllowedTypeSlots()
    {
        echo "Creating Allowed Types\n";
        $fields = [
            'id' => $this->defaultIdField,
            'slot_id' => [
                'type' => 'INT',
                'unsigned'=> true,
                'null' => false
            ],
            'allowed_type_id' => [
                'type' => 'INT',
                'unsigned'=> true,
                'null' => false
            ]
        ];
        $fields = $this->addTimestamps($fields);
        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('slot_id', 'type_slots', 'id');
        $this->forge->addForeignKey('allowed_type_id', 'equipment_types', 'id');
        $this->forge->addUniqueKey(['slot_id', 'allowed_type_id']);
        $this->forge->createTable('allowed_type_slots', true);

        $this->forge->addField($this->getHistFields($fields));
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('slot_id', 'type_slots', 'id');
        $this->forge->addForeignKey('allowed_type_id', 'equipment_types', 'id');
        $this->forge->createTable('allowed_type_slots_hist', true);

        echo "Created Allowed Types\n";
    }

    public function up()
    {
        $this->createEquipmentTypes();
        $this->createTypeSlots();
        $this->createAllowedTypeSlots();
        $this->createInstitutes();
        $this->createLocations();
        $this->createStatuses();
        $this->createFiles();
        $this->createEquipments();
        $this->ParentChildEquips();
        $this->createEquipments2Files();
    }

    public function down()
    {
        // Drop tables in reverse order
        $this->forge->dropTable('equipments_to_files', true);
        $this->forge->dropTable('equipments_to_files_hist', true);
        $this->forge->dropTable('parent_child_equips', true);
        $this->forge->dropTable('parent_child_equips_hist', true);
        $this->forge->dropTable('equipments', true);
        $this->forge->dropTable('equipments_hist', true);
        $this->forge->dropTable('files', true);
        $this->forge->dropTable('files_hist', true);
        $this->forge->dropTable('institutes', true);
        $this->forge->dropTable('institutes_hist', true);
        $this->forge->dropTable('locations', true);
        $this->forge->dropTable('locations_hist', true);
        $this->forge->dropTable('statuses', true);
        $this->forge->dropTable('statuses_hist', true);
        $this->forge->dropTable('allowed_type_slots', true);
        $this->forge->dropTable('allowed_type_slots_hist', true);
        $this->forge->dropTable('type_slots', true);
        $this->forge->dropTable('type_slots_hist', true);
        $this->forge->dropTable('equipment_types', true);
        $this->forge->dropTable('equipment_types_hist', true);
    }
}
