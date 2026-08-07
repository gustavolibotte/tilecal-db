<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBoxPositionToPmt extends Migration
{
    public function up()
    {
        // Update the JSON type_params adding the 'box_position' key with a NULL value
        // for all PMT equipments (type_id = 1) to ensure consistency.
        $sql = "UPDATE equipments 
                SET type_params = JSON_SET(COALESCE(type_params, '{}'), '$.box_position', NULL) 
                WHERE type_id = 1";
                
        $this->db->query($sql);
    }

    public function down()
    {
        // Remove the 'box_position' key in case of migration rollback.
        $sql = "UPDATE equipments 
                SET type_params = JSON_REMOVE(type_params, '$.box_position') 
                WHERE type_id = 1";
                
        $this->db->query($sql);
    }
}