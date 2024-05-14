<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateFileRelationTriggers extends Migration
{
    public function up()
    {
        $this->db->query("
            DROP TRIGGER IF EXISTS `equipments_to_files_hist_update`;
        ");
        $this->db->query("
            CREATE TRIGGER equipments_to_files_hist_delete
            AFTER DELETE ON equipments_to_files
            FOR EACH ROW
            BEGIN
                INSERT INTO equipments_to_files_hist(`user`, `action`, record_id, file_id, equipment_id, created_at)
                VALUES (OLD.user, 'delete', OLD.id, OLD.file_id, OLD.equipment_id, OLD.created_at);
            END;
        ");
    }

    public function down()
    {
        $this->db->query("
        DROP TRIGGER IF EXISTS `equipments_to_files_hist_delete`;
        ");
    $this->db->query("
        CREATE TRIGGER equipments_to_files_hist_update
        AFTER UPDATE ON equipments_to_files
        FOR EACH ROW
        BEGIN
            DECLARE action_var VARCHAR(7);
            SET action_var = get_action_from_update(NEW.deleted_at, OLD.deleted_at, NEW.updated_at, OLD.updated_at);
            INSERT INTO equipments_to_files_hist(`user`, `action`, record_id, file_id, equipment_id, created_at)
            VALUES (NEW.user, action_var, NEW.id, NEW.file_id, NEW.equipment_id, NEW.updated_at);
        END;
    ");
    }
}
