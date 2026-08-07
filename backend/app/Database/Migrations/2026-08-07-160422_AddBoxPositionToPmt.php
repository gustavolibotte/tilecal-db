<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBoxPositionToPmt extends Migration
{
    public function up()
    {
        // Set MySQL session variables required by the history triggers.
        // Since CLI migrations lack HTTP context, we must provide them manually.
        $this->db->query("SET @action = 'update'");
        $this->db->query("SET @user_id = 1"); 
        $this->db->query("SET @username = 'glibotte'");

        // Update the JSON type_params adding the 'box_position' key with a JSON null value
        // CAST('null' AS JSON) ensures a literal null is injected safely inside the JSON object
        $sql = "UPDATE equipments 
                SET type_params = JSON_SET(COALESCE(type_params, '{}'), '$.box_position', CAST('null' AS JSON)) 
                WHERE type_id = 1";
                
        $this->db->query($sql);
    }

    public function down()
    {
        // Set session variables for the rollback as well
        $this->db->query("SET @action = 'update'");
        $this->db->query("SET @user_id = 1");
        $this->db->query("SET @username = 'glibotte'");

        // Remove the 'box_position' key in case of migration rollback
        $sql = "UPDATE equipments 
                SET type_params = JSON_REMOVE(type_params, '$.box_position') 
                WHERE type_id = 1";
                
        $this->db->query($sql);
    }
}