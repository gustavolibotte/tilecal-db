<?php

namespace App\Database\Migrations;

use App\Library\BaseMigration;
use CodeIgniter\Database\RawSql;

class HistRefact extends BaseMigration
{
    protected array $userColumn = [
        'user' => [
            'type' => 'VARCHAR',
            'constraint' => 10,
            'null' => false,
        ]
    ];

    protected array $modifiedTimestamps;

    public function up()
    {
        $this->modifiedTimestamps = [
            'created_at' => [
                'type' => 'TIMESTAMP',
                'constraint' => 6,
                'default' => new RawSql('NOW(6)'),
                'null' => false
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'constraint' => 6,
                'default' => new RawSql('NOW(6) ON UPDATE NOW(6)'),
                'null' => false
            ]
        ];
        $this->createFunctions();
        $this->createParentChildEquipsTriggers();
        $this->createEquipmentsToFilesTriggers();
        $this->createDetectorLocationTriggers();
        $this->createAllowedTypeSlotsTriggers();
        $this->createTypeSlotsTrigger();
        $this->createEquipmentTypesTriggers();
        $this->createInstitutesTriggers();
        $this->createEquipmentsTriggers();
        $this->createFilesTriggers();
        $this->createStatusesTriggers();
        $this->createLocationsTriggers();
    }

    public function down()
    {
        $this->dropLocationsTriggers();
        $this->dropStatusesTriggers();
        $this->dropFilesTriggers();
        $this->dropEquipmentsTriggers();
        $this->dropInstitutesTriggers();
        $this->dropEquipmentTypesTriggers();
        $this->dropTypeSlotsTriggers();
        $this->dropAllowedTypeSotsTriggers();
        $this->dropDetectorLocationsTriggers();
        $this->dropEquipmentsToFilesTriggers();
        $this->dropParentChildEquipsTrigger();
        $this->dropFunctions();
    }

    protected function createFunctions()
    {
        $this->db->query("
            CREATE FUNCTION get_action_from_update(
                new_deleted_at TIMESTAMP,
                old_deleted_at TIMESTAMP,
                new_updated_at TIMESTAMP,
                old_updated_at TIMESTAMP) RETURNS VARCHAR(7) DETERMINISTIC
            BEGIN
                DECLARE action_var VARCHAR(7);
            
                IF new_deleted_at IS NULL AND old_deleted_at IS NOT NULL THEN 
                    SET action_var = 'restore';
                ELSEIF new_deleted_at IS NOT NULL AND old_deleted_at IS NULL THEN
                    SET action_var = 'delete';
                ELSEIF new_deleted_at IS NULL AND new_updated_at != old_updated_at THEN
                    SET action_var = 'update';
                END IF;

                RETURN action_var;
            END;
        ");
    }

    protected function createParentChildEquipsTriggers()
    {
        $this->forge->addColumn('parent_child_equips', $this->userColumn);
        $this->forge->modifyColumn('parent_child_equips', $this->modifiedTimestamps);
        $this->db->query("
            CREATE TRIGGER parent_child_equips_hist_insert
            AFTER INSERT ON parent_child_equips
            FOR EACH ROW
            BEGIN
                INSERT INTO parent_child_equips_hist(`user`, `action`, record_id, parent_equip_id, child_equip_id, slot_id, created_at)
                VALUES (NEW.user, 'insert', NEW.id, NEW.parent_equip_id, NEW.child_equip_id, NEW.slot_id, NEW.created_at);
            END;
        ");

        $this->db->query("
            CREATE TRIGGER parent_child_equips_hist_update
            AFTER UPDATE ON parent_child_equips
            FOR EACH ROW
            BEGIN
                DECLARE action_var VARCHAR(7);
                SET action_var = get_action_from_update(NEW.deleted_at, OLD.deleted_at, NEW.updated_at, OLD.updated_at);
                INSERT INTO parent_child_equips_hist(`user`, `action`, record_id, parent_equip_id, child_equip_id, slot_id, created_at)
                VALUES (NEW.user, action_var, NEW.id, NEW.parent_equip_id, NEW.child_equip_id, NEW.slot_id, NEW.created_at);
            END;
        ");
    }

    protected function createEquipmentsToFilesTriggers()
    {
        $this->forge->addColumn('equipments_to_files', $this->userColumn);
        $this->forge->modifyColumn('equipments_to_files', $this->modifiedTimestamps);
        $this->db->query("
            CREATE TRIGGER equipments_to_files_hist_insert
            AFTER INSERT ON equipments_to_files
            FOR EACH ROW
            BEGIN
                INSERT INTO equipments_to_files_hist(`user`, `action`, record_id, file_id, equipment_id, created_at)
                VALUES (NEW.user, 'insert', NEW.id, NEW.file_id, NEW.equipment_id, NEW.created_at);
            END;
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

    protected function createDetectorLocationTriggers()
    {
        $this->forge->addColumn('detector_locations', $this->userColumn);
        $this->forge->modifyColumn('detector_locations', $this->modifiedTimestamps);
        $this->db->query("
            CREATE TRIGGER detector_locations_hist_insert
            AFTER INSERT ON detector_locations
            FOR EACH ROW
            BEGIN
                INSERT INTO detector_locations_hist(`user`, `action`, record_id, name, created_at)
                VALUES (NEW.user, 'insert', NEW.id, NEW.name, NEW.created_at);
            END;
        ");

        $this->db->query("
            CREATE TRIGGER detector_locations_hist_update
            AFTER UPDATE ON detector_locations
            FOR EACH ROW
            BEGIN
                DECLARE action_var VARCHAR(7);
                SET action_var = get_action_from_update(NEW.deleted_at, OLD.deleted_at, NEW.updated_at, OLD.updated_at);
                INSERT INTO detector_locations_hist(`user`, `action`, record_id, name, created_at)
                VALUES (NEW.user, action_var, NEW.id, NEW.name, NEW.updated_at);
            END;
        ");
    }

    protected function createAllowedTypeSlotsTriggers()
    {
        $this->forge->addColumn('allowed_type_slots', $this->userColumn);
        $this->forge->modifyColumn('allowed_type_slots', $this->modifiedTimestamps);
        $this->db->query("
            CREATE TRIGGER allowed_type_slots_hist_insert
            AFTER INSERT ON allowed_type_slots
            FOR EACH ROW
            BEGIN
                INSERT INTO allowed_type_slots_hist(`user`, `action`, record_id, slot_id, allowed_type_id, created_at)
                VALUES (NEW.user, 'insert', NEW.id, NEW.slot_id, NEW.allowed_type_id, NEW.created_at);
            END;
        ");

        $this->db->query("
            CREATE TRIGGER allowed_type_slots_hist_update
            AFTER UPDATE ON allowed_type_slots
            FOR EACH ROW
            BEGIN
                DECLARE action_var VARCHAR(7);
                SET action_var = get_action_from_update(NEW.deleted_at, OLD.deleted_at, NEW.updated_at, OLD.updated_at);
                INSERT INTO allowed_type_slots_hist(`user`, `action`, record_id, slot_id, allowed_type_id, created_at)
                VALUES (NEW.user, action_var, NEW.id, NEW.slot_id, NEW.allowed_type_id, NEW.updated_at);
            END;
        ");
    }

    protected function createTypeSlotsTrigger()
    {
        $this->forge->addColumn('type_slots', $this->userColumn);
        $this->forge->modifyColumn('type_slots', $this->modifiedTimestamps);
        $this->db->query("
            CREATE TRIGGER type_slots_hist_insert
            AFTER INSERT ON type_slots
            FOR EACH ROW
            BEGIN
                INSERT INTO type_slots_hist(`user`, `action`, record_id, name, parent_type_id, created_at)
                VALUES (NEW.user, 'insert', NEW.id, NEW.name, NEW.parent_type_id, NEW.created_at);
            END;
        ");

        $this->db->query("
            CREATE TRIGGER type_slots_hist_update
            AFTER UPDATE ON type_slots
            FOR EACH ROW
            BEGIN
                DECLARE action_var VARCHAR(7);
                SET action_var = get_action_from_update(NEW.deleted_at, OLD.deleted_at, NEW.updated_at, OLD.updated_at);
                INSERT INTO type_slots_hist(`user`, `action`, record_id, name, parent_type_id, created_at)
                VALUES (NEW.user, action_var, NEW.id, NEW.name, NEW.parent_type_id, NEW.updated_at);
            END;
        ");
    }

    protected function createEquipmentTypesTriggers()
    {
        $this->forge->addColumn('equipment_types', $this->userColumn);
        $this->forge->modifyColumn('equipment_types', $this->modifiedTimestamps);
        $this->db->query("
            CREATE TRIGGER equipment_types_hist_insert
            AFTER INSERT ON equipment_types
            FOR EACH ROW
            BEGIN
                INSERT INTO equipment_types_hist(`user`, `action`, record_id, name, in_detector, project, `system`, id_regex, label, params, active, description, created_at)
                VALUES (NEW.user, 'insert', NEW.id, NEW.name, NEW.in_detector, NEW.project, NEW.system, NEW.id_regex, NEW.label, NEW.params, NEW.active, NEW.description, NEW.created_at);
            END;
        ");

        $this->db->query("
            CREATE TRIGGER equipment_types_hist_update
            AFTER UPDATE ON equipment_types
            FOR EACH ROW
            BEGIN
                DECLARE action_var VARCHAR(7);
                SET action_var = get_action_from_update(NEW.deleted_at, OLD.deleted_at, NEW.updated_at, OLD.updated_at);
                INSERT INTO equipment_types_hist(`user`, `action`, record_id, name, in_detector, project, `system`, id_regex, label, params, active, description, created_at)
                VALUES (NEW.user, action_var, NEW.id, NEW.name, NEW.in_detector, NEW.project, NEW.system, NEW.id_regex, NEW.label, NEW.params, NEW.active, NEW.description, NEW.updated_at);
            END;
        ");
    }

    protected function createLocationsTriggers()
    {
        $this->forge->addColumn('locations', $this->userColumn);
        $this->forge->modifyColumn('locations', $this->modifiedTimestamps);
        $this->db->query("
            CREATE TRIGGER locations_hist_insert
            AFTER INSERT ON locations
            FOR EACH ROW
            BEGIN
                INSERT INTO locations_hist(`user`, `action`, record_id, name, institute_id, description, created_at)
                VALUES (NEW.user, 'insert', NEW.id, NEW.name, NEW.institute_id, NEW.description, NEW.created_at);
            END;
        ");

        $this->db->query("
            CREATE TRIGGER locations_hist_update
            AFTER UPDATE ON locations
            FOR EACH ROW
            BEGIN
                DECLARE action_var VARCHAR(7);
                SET action_var = get_action_from_update(NEW.deleted_at, OLD.deleted_at, NEW.updated_at, OLD.updated_at);
                INSERT INTO locations_hist(`user`, `action`, record_id, name, institute_id, description, created_at)
                VALUES (NEW.user, action_var, NEW.id, NEW.name, NEW.institute_id, NEW.description, NEW.updated_at);
            END;
        ");
    }

    protected function createInstitutesTriggers()
    {
        $this->forge->addColumn('institutes', $this->userColumn);
        $this->forge->modifyColumn('institutes', $this->modifiedTimestamps);
        $this->db->query("
            CREATE TRIGGER institutes_hist_insert
            AFTER INSERT ON institutes
            FOR EACH ROW
            BEGIN
                INSERT INTO institutes_hist(`user`, `action`, record_id, name, mne, country, created_at)
                VALUES (NEW.user, 'insert', NEW.id, NEW.name, NEW.mne, NEW.country, NEW.created_at);
            END;
        ");

        $this->db->query("
            CREATE TRIGGER institutes_hist_update
            AFTER UPDATE ON institutes
            FOR EACH ROW
            BEGIN
                DECLARE action_var VARCHAR(7);
                SET action_var = get_action_from_update(NEW.deleted_at, OLD.deleted_at, NEW.updated_at, OLD.updated_at);
                INSERT INTO institutes_hist(`user`, `action`, record_id, name, mne, country, created_at)
                VALUES (NEW.user, 'insert', NEW.id, NEW.name, NEW.mne, NEW.country, NEW.updated_at);
            END;
        ");
    }

    protected function createEquipmentsTriggers()
    {
        $this->forge->addColumn('equipments', $this->userColumn);
        $this->forge->modifyColumn('equipments', $this->modifiedTimestamps);
        $this->db->query("
            CREATE TRIGGER equipments_hist_insert
            AFTER INSERT ON equipments
            FOR EACH ROW
            BEGIN
                INSERT INTO equipments_hist(`user`, `action`, record_id, type_id, owner_id, location_id, detector_location_id, status_id, serial, type_params, comments, created_at)
                VALUES (NEW.user, 'insert', NEW.id, NEW.type_id, NEW.owner_id, NEW.location_id, NEW.detector_location_id, NEW.status_id, NEW.serial, NEW.type_params, NEW.comments, NEW.created_at);
            END;
        ");

        $this->db->query("
            CREATE TRIGGER equipments_hist_update
            AFTER UPDATE ON equipments
            FOR EACH ROW
            BEGIN
                DECLARE action_var VARCHAR(7);
                SET action_var = get_action_from_update(NEW.deleted_at, OLD.deleted_at, NEW.updated_at, OLD.updated_at);
                INSERT INTO equipments_hist(`user`, `action`, record_id, type_id, owner_id, location_id, detector_location_id, status_id, serial, type_params, comments, created_at)
                VALUES (NEW.user, action_var, NEW.id, NEW.type_id, NEW.owner_id, NEW.location_id, NEW.detector_location_id, NEW.status_id, NEW.serial, NEW.type_params, NEW.comments, NEW.updated_at);
            END;
        ");
    }

    protected function createFilesTriggers()
    {
        $this->forge->addColumn('files', $this->userColumn);
        $this->forge->modifyColumn('files', $this->modifiedTimestamps);
        $this->db->query("
            CREATE TRIGGER files_hist_insert
            AFTER INSERT ON files
            FOR EACH ROW
            BEGIN
                INSERT INTO files_hist(`user`, `action`, record_id, path, name, description, created_at)
                VALUES (NEW.user, 'insert', NEW.id, NEW.path, NEW.name, NEW.description, NEW.created_at);
            END;
        ");

        $this->db->query("
            CREATE TRIGGER files_hist_update
            AFTER UPDATE ON files
            FOR EACH ROW
            BEGIN
                DECLARE action_var VARCHAR(7);
                SET action_var = get_action_from_update(NEW.deleted_at, OLD.deleted_at, NEW.updated_at, OLD.updated_at);
                INSERT INTO files_hist(`user`, `action`, record_id, path, name, description, created_at)
                VALUES (NEW.user, 'insert', NEW.id, NEW.path, NEW.name, NEW.description, NEW.updated_at);
            END;
        ");
    }

    protected function createStatusesTriggers()
    {
        $this->forge->addColumn('statuses', $this->userColumn);
        $this->forge->modifyColumn('statuses', $this->modifiedTimestamps);
        $this->db->query("
            CREATE TRIGGER statuses_hist_insert
            AFTER INSERT ON statuses
            FOR EACH ROW
            BEGIN
                INSERT INTO statuses_hist(`user`, `action`, record_id, name, description, created_at)
                VALUES (NEW.user, 'insert', NEW.id, NEW.name, NEW.description, NEW.created_at);
            END;
        ");

        $this->db->query("
            CREATE TRIGGER statuses_hist_update
            AFTER UPDATE ON statuses
            FOR EACH ROW
            BEGIN
                DECLARE action_var VARCHAR(7);
                SET action_var = get_action_from_update(NEW.deleted_at, OLD.deleted_at, NEW.updated_at, OLD.updated_at);
                INSERT INTO statuses_hist(`user`, `action`, record_id, name, description, created_at)
                VALUES (NEW.user, action_var, NEW.id, NEW.name, NEW.description, NEW.updated_at);
            END;
        ");
    }

    protected function dropParentChildEquipsTrigger()
    {
        $this->forge->dropColumn('parent_child_equips', 'user');
        $this->db->query("
            DROP TRIGGER IF EXISTS `parent_child_equips_hist_insert`;
        ");

        $this->db->query("
            DROP TRIGGER IF EXISTS `parent_child_equips_hist_update`;
        ");
    }

    protected function dropEquipmentsToFilesTriggers()
    {
        $this->forge->dropColumn('equipments_to_files', 'user');
        $this->db->query("
            DROP TRIGGER IF EXISTS `equipments_to_files_hist_insert`;
        ");

        $this->db->query("
            DROP TRIGGER IF EXISTS `equipments_to_files_hist_update`;
        ");
    }

    protected function dropDetectorLocationsTriggers()
    {
        $this->forge->dropColumn('detector_locations', 'user');
        $this->db->query("
            DROP TRIGGER IF EXISTS `detector_locations_hist_insert`;
        ");

        $this->db->query("
            DROP TRIGGER IF EXISTS `detector_locations_hist_update`;
        ");
    }

    protected function dropAllowedTypeSotsTriggers()
    {
        $this->forge->dropColumn('allowed_type_slots', 'user');
        $this->db->query("
            DROP TRIGGER IF EXISTS `allowed_type_slots_hist_insert`;
        ");

        $this->db->query("
            DROP TRIGGER IF EXISTS `allowed_type_slots_hist_update`;
        ");
    }

    protected function dropTypeSlotsTriggers()
    {
        $this->forge->dropColumn('type_slots', 'user');
        $this->db->query("
            DROP TRIGGER IF EXISTS `type_slots_hist_insert`;
        ");

        $this->db->query("
            DROP TRIGGER IF EXISTS `type_slots_hist_update`;
        ");
    }

    protected function dropEquipmentTypesTriggers()
    {
        $this->forge->dropColumn('equipment_types', 'user');
        $this->db->query("
            DROP TRIGGER IF EXISTS `equipment_types_hist_insert`;
        ");

        $this->db->query("
            DROP TRIGGER IF EXISTS `equipment_types_hist_update`;
        ");
    }

    protected function dropLocationsTriggers()
    {
        $this->forge->dropColumn('locations', 'user');
        $this->db->query("
            DROP TRIGGER IF EXISTS `locations_hist_insert`;
        ");

        $this->db->query("
            DROP TRIGGER IF EXISTS `locations_hist_update`;
        ");
    }

    protected function dropInstitutesTriggers()
    {
        $this->forge->dropColumn('institutes', 'user');
        $this->db->query("
            DROP TRIGGER IF EXISTS `institutes_hist_insert`;
        ");

        $this->db->query("
            DROP TRIGGER IF EXISTS `institutes_hist_update`;
        ");
    }

    protected function dropEquipmentsTriggers()
    {
        $this->forge->dropColumn('equipments', 'user');
        $this->db->query("
            DROP TRIGGER IF EXISTS `equipments_hist_insert`;
        ");

        $this->db->query("
            DROP TRIGGER IF EXISTS `equipments_hist_update`;
        ");
    }

    protected function dropFilesTriggers()
    {
        $this->forge->dropColumn('files', 'user');
        $this->db->query("
            DROP TRIGGER IF EXISTS `files_hist_insert`;
        ");

        $this->db->query("
            DROP TRIGGER IF EXISTS `files_hist_update`;
        ");
    }

    protected function dropStatusesTriggers()
    {
        $this->forge->dropColumn('statuses', 'user');
        $this->db->query("
            DROP TRIGGER IF EXISTS `statuses_hist_insert`;
        ");
        $this->db->query("
            DROP TRIGGER IF EXISTS `statuses_hist_update`;
        ");
    }

    protected function dropFunctions()
    {
        $this->db->query("
            DROP FUNCTION IF EXISTS get_action_from_update;
        ");

        $this->db->query("
            DROP FUNCTION IF EXISTS get_deleted_at_from_update;
        ");
    }
}
