<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDatabase extends Migration
{   
    public function createModels(){
        $this->forge->addField('id INT UNSIGNED AUTO_INCREMENT');
        $this->forge->addField('name VARCHAR(50) NOT NULL');
        $this->forge->addField('formatted_name VARCHAR(50) NOT NULL');
        $this->forge->addField('description TEXT');
        $this->forge->addField('updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL');
        $this->forge->addField('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->forge->addField('CONSTRAINT unique_model_name UNIQUE(name)');
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('models', true);
    }

    public function createModelsHist(){
        $this->forge->addField('id INT UNSIGNED AUTO_INCREMENT');
        $this->forge->addField('record_id INT UNSIGNED NOT NULL');
        $this->forge->addField('user VARCHAR(20) NOT NULL');
        $this->forge->addField("action ENUM('insert', 'update', 'delete') NOT NULL");
        $this->forge->addField('changes JSON NOT NULL');
        $this->forge->addField('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('models_hist', true);
    }

    public function createFiles(){
        $this->forge->addField('id INT UNSIGNED AUTO_INCREMENT');
        $this->forge->addField('name VARCHAR(100) NOT NULL');
        $this->forge->addField('mime VARCHAR(50) NOT NULL');
        $this->forge->addField('extension VARCHAR(10) NOT NULL');
        $this->forge->addField('filedata MEDIUMBLOB NOT NULL');
        $this->forge->addField('description TEXT NOT NULL');
        $this->forge->addField('updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL');
        $this->forge->addField('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->forge->addField('CONSTRAINT unique_file_name UNIQUE(name)');
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('files', true);
    }

    public function createFilesHist(){
        $this->forge->addField('id INT UNSIGNED AUTO_INCREMENT');
        $this->forge->addField('record_id INT UNSIGNED NOT NULL');
        $this->forge->addField('user VARCHAR(20) NOT NULL');
        $this->forge->addField("action ENUM('insert', 'update', 'delete') NOT NULL");
        $this->forge->addField('changes JSON NOT NULL');
        $this->forge->addField('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('files_hist', true);
    }

    public function createEquipments(){
        $this->forge->addField('id INT UNSIGNED NOT NULL');
        $this->forge->addField('model_id INT UNSIGNED NOT NULL');
        $this->forge->addField('location VARCHAR(30) NOT NULL');
        $this->forge->addField('status VARCHAR(30) NOT NULL');
        $this->forge->addField('parent_id INT UNSIGNED');
        $this->forge->addField('serial VARCHAR(20)');
        $this->forge->addField('comments TEXT');
        $this->forge->addField('extra_params JSON');
        $this->forge->addField('updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL');
        $this->forge->addField('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('model_id', 'models', 'id');
        $this->forge->addForeignKey('parent_id', 'equipments', 'id');
        $this->forge->createTable('equipments', true);
    }

    public function createEquipmentsHist(){
        $this->forge->addField('id INT UNSIGNED AUTO_INCREMENT');
        $this->forge->addField('record_id INT UNSIGNED NOT NULL');
        $this->forge->addField('user VARCHAR(20) NOT NULL');
        $this->forge->addField("action ENUM('insert', 'update', 'delete') NOT NULL");
        $this->forge->addField('changes JSON NOT NULL');
        $this->forge->addField('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('equipments_hist', true);
    }

    public function createEquipments2Files(){
        $this->forge->addField('id INT UNSIGNED AUTO_INCREMENT');
        $this->forge->addField('equipment_id INT UNSIGNED NOT NULL');
        $this->forge->addField('file_id INT UNSIGNED NOT NULL');
        $this->forge->addField('updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL');
        $this->forge->addField('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('equipment_id', 'equipments', 'id');
        $this->forge->addForeignKey('file_id', 'files', 'id');
        $this->forge->createTable('equipments_to_files', true);
    }

    public function createEquipments2FilesHist(){
        $this->forge->addField('id INT UNSIGNED AUTO_INCREMENT');
        $this->forge->addField('record_id INT UNSIGNED NOT NULL');
        $this->forge->addField('user VARCHAR(20) NOT NULL');
        $this->forge->addField("action ENUM('insert', 'update', 'delete') NOT NULL");
        $this->forge->addField('changes JSON NOT NULL');
        $this->forge->addField('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('equipments_to_files_hist', true);
    }

    public function up()
    {
        $this->createModels();
        $this->createModelsHist();
        $this->createFiles();
        $this->createFilesHist();
        $this->createEquipments();
        $this->createEquipmentsHist();
        $this->createEquipments2Files();
        $this->createEquipments2FilesHist();
    }

    public function down()
    {
        // Drop tables in reverse order
        $this->forge->dropTable('equipments_to_files_hist', true);
        $this->forge->dropTable('equipments_to_files', true);
        $this->forge->dropTable('equipments_hist', true);
        $this->forge->dropTable('equipments', true);
        $this->forge->dropTable('files_hist', true);
        $this->forge->dropTable('files', true);
        $this->forge->dropTable('models_hist', true);
        $this->forge->dropTable('models', true);

    }
}
