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
                'null' => false,
                'default' => new RawSql('CURRENT_TIMESTAMP')
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
                'default' => new RawSql('CURRENT_TIMESTAMP')
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
        echo "Creating Models\n";

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
        $this->forge->createTable('models', true);

        $jsonData = readJson(joinPaths([ROOTPATH, 'data', '2023-07-07-134759_models.json']), true);
        $modelModel = new \App\Models\ModelModel();
        foreach ($jsonData as $row){
            $model = new \App\Entities\Model($row);
            $modelModel->save($model);
        }

        echo "Created Models\n";

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
        $this->forge->createTable('institutes', true);
        
        $jsonData = readJson(joinPaths([ROOTPATH, 'data', '2023-07-07-134759_institutes.json']), true);
        $instituteModel = new \App\Models\InstituteModel();
        foreach ($jsonData as $row){
            $institute = new \App\Entities\Institute($row);
            $instituteModel->save($institute);
        }

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

        $jsonData = readJson(joinPaths([ROOTPATH, 'data', '2023-07-07-134759_locations.json']), true);
        $instituteModel = new \App\Models\InstituteModel();
        $locationModel = new \App\Models\LocationModel();
        foreach ($jsonData as $row){
            $institute = $instituteModel->where('mne', $row['institute_mne'])->first();
            $location = new \App\Entities\Location($row);
            if (!is_null($institute)){
                $location->institute_id = $institute->id; 
            }
            $locationModel->save($location);
        }
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

        $jsonData = readJson(joinPaths([ROOTPATH, 'data', '2023-07-07-134759_statuses.json']), true);
        $statusModel = new \App\Models\StatusModel();
        foreach ($jsonData as $row){
            $status = new \App\Entities\Status($row);
            $statusModel->save($status);
        }

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
            'model_id' => [
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
            'parent_id' => [
                'type' => 'CHAR',
                'constraint' => 14,
                'null' => true
            ],
            'serial' => [
                'type' => 'VARCHAR',
                'constraint' => 36, # Number of chars in uuid,
                'null' => true
            ],
            'model_params' => [
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
        $this->forge->addForeignKey('model_id', 'models', 'id');
        $this->forge->addForeignKey('owner_id', 'institutes', 'id');
        $this->forge->addForeignKey('parent_id', 'equipments', 'id');
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
                'default' => new RawSql('CURRENT_TIMESTAMP')
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('hist', true);
        echo "Created Hist\n";
    }

    public function up()
    {
        helper(['json', 'path']);
        $this->createHist();
        $this->createModels();
        $this->createInstitutes();
        $this->createLocations();
        $this->createStatuses();
        $this->createFiles();
        $this->createEquipments();
        $this->createEquipments2Files();
    }

    public function down()
    {
        // Drop tables in reverse order
        $this->forge->dropTable('hist', true);
        $this->forge->dropTable('equipments_to_files', true);
        $this->forge->dropTable('equipments', true);
        $this->forge->dropTable('files', true);
        $this->forge->dropTable('institutes', true);
        $this->forge->dropTable('locations', true);
        $this->forge->dropTable('statuses', true);
        $this->forge->dropTable('models', true);
    }
}
