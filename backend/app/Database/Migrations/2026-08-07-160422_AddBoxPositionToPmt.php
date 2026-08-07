<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBoxPositionToPmt extends Migration
{
    public function up()
    {
        $sql = "UPDATE equipments 
                SET type_params = JSON_SET(COALESCE(type_params, '{}'), '$.box_position', CAST('null' AS JSON)),
                    user = 'glibotte',
                    updated_at = NOW(6)
                WHERE type_id = 1";
                
        $this->db->query($sql);
    }

    public function down()
    {
        $sql = "UPDATE equipments 
                SET type_params = JSON_REMOVE(type_params, '$.box_position'),
                    user = 'glibotte',
                    updated_at = NOW(6)
                WHERE type_id = 1";
                
        $this->db->query($sql);
    }
}