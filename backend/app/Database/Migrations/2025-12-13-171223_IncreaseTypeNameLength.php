<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class IncreaseTypeNameLength extends Migration
{
    public function up()
    {
        // Modify the 'name' column in 'equipment_types' table to VARCHAR(100)
        // This is necessary to support longer equipment names like 
        // "Active HV Divider (Long Cable)" which exceeds the previous 30 char limit.
        $fields = [
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
        ];
        
        // Apply modification to the main table
        $this->forge->modifyColumn('equipment_types', $fields);

        // Apply modification to the history table to maintain consistency
        // and prevent errors when updates trigger a history insert.
        $this->forge->modifyColumn('equipment_types_hist', $fields);
    }

    public function down()
    {
        // Revert back to VARCHAR(30) if we need to rollback this migration
        $fields = [
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
        ];
        
        $this->forge->modifyColumn('equipment_types', $fields);
        $this->forge->modifyColumn('equipment_types_hist', $fields);
    }
}