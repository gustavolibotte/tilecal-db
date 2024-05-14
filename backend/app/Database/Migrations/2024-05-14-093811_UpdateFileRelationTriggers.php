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
            DROP TRIGGER IF EXISTS `parent_child_equips_hist_delete`;
        ");
        $this->db->query("
            CREATE TRIGGER equipments_to_files_hist_delete
            AFTER DELETE ON equipments_to_files
            FOR EACH ROW
            BEGIN
                INSERT INTO equipments_to_files_hist(`user`, `action`, record_id, file_id, equipment_id, created_at)
                VALUES (OLD.user, 'delete', OLD.id, OLD.file_id, OLD.equipment_id, CURRENT_TIMESTAMP);
            END;
        ");
        $this->db->query("
            CREATE TRIGGER `parent_child_equips_hist_delete`
            AFTER DELETE ON `parent_child_equips`
            FOR EACH ROW
            BEGIN
                INSERT INTO parent_child_equips_hist(`user`, `action`, record_id, parent_equip_id, child_equip_id, slot_id, created_at)
                VALUES (OLD.user, 'delete', OLD.id, OLD.parent_equip_id, OLD.child_equip_id, OLD.slot_id, CURRENT_TIMESTAMP);
            END
        ");
    }

    public function down()
    {
        $this->db->query("
        DROP TRIGGER IF EXISTS `equipments_to_files_hist_delete`;
        ");
        $this->db->query("
            DROP TRIGGER IF EXISTS `parent_child_equips_hist_delete`;
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
        $this->db->query("
            CREATE TRIGGER `parent_child_equips_hist_delete`
            AFTER DELETE ON `parent_child_equips`
            FOR EACH ROW
            BEGIN
                INSERT INTO parent_child_equips_hist(`user`, `action`, record_id, parent_equip_id, child_equip_id, slot_id, created_at)
                VALUES (OLD.user, 'delete', OLD.id, OLD.parent_equip_id, OLD.child_equip_id, OLD.slot_id, OLD.created_at);
            END
        ");
    }
}
