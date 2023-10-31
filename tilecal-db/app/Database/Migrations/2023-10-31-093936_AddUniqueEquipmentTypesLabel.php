<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueEquipmentTypesLabel extends Migration
{
    public function up()
    {
        $this->forge->addUniqueKey('label', 'equipment_types_unique_labels');
        $this->forge->processIndexes('equipment_types');
    }

    public function down()
    {
        $this->forge->dropKey('equipment_types', 'equipment_types_unique_labels');
        $this->forge->processIndexes('equipment_types');
    }
}
