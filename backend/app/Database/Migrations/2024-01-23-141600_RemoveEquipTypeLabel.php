<?php

namespace App\Database\Migrations;

use App\Library\BaseMigration;

class RemoveEquipTypeLabel extends BaseMigration
{
    public function up()
    {

        $this->dbconnect->transException(true)->transBegin();
        $this->dbconnect->query($this->getUpQuery());
        $this->forge->dropColumn('equipment_types', 'label');
        $this->forge->dropColumn('equipment_types_hist', 'label');
        $this->upTriggers();
        $this->dbconnect->transCommit();
    }

    public function down()
    {

        $this->dbconnect->transException(true)->transBegin();
        $labelField = [
            'label' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => false,
                'unique' => true,
                'after' => 'name'
            ]
        ];
        $this->forge->addColumn('equipment_types', $labelField);
        $this->forge->addColumn('equipment_types_hist', $labelField);
        $this->downTriggers();
        $this->dbconnect->query($this->getDownQuery());
        $this->dbconnect->transCommit();
    }

    protected function getUpQuery(): string
    {
        $authenticator = \Config\Services::authenticator();
        $user = $authenticator->getUser();

        $updateQuery = sprintf(
            'UPDATE equipment_types SET user=%s, name=label',
            $this->db->escape($user->username)
        );
        return $updateQuery;
    }

    protected function getDownQuery(): string
    {
        $authenticator = \Config\Services::authenticator();
        $user = $authenticator->getUser();

        $updateQuery = sprintf(
            'UPDATE equipment_types SET user=%s, label=name',
            $this->db->escape($user->username)
        );
        return $updateQuery;
    }

    protected function dropTriggers()
    {
        $this->db->query("
        DROP TRIGGER IF EXISTS `equipment_types_hist_insert`;
        ");

        $this->db->query("
            DROP TRIGGER IF EXISTS `equipment_types_hist_update`;
        ");
    }

    protected function upTriggers()
    {
        $this->dropTriggers();
        $this->db->query("
            CREATE TRIGGER equipment_types_hist_insert
            AFTER INSERT ON equipment_types
            FOR EACH ROW
            BEGIN
                INSERT INTO equipment_types_hist(`user`, `action`, record_id, name, in_detector, project, `system`, id_regex, params, active, description, created_at)
                VALUES (NEW.user, 'insert', NEW.id, NEW.name, NEW.in_detector, NEW.project, NEW.system, NEW.id_regex, NEW.params, NEW.active, NEW.description, NEW.created_at);
            END;
        ");

        $this->db->query("
            CREATE TRIGGER equipment_types_hist_update
            AFTER UPDATE ON equipment_types
            FOR EACH ROW
            BEGIN
                DECLARE action_var VARCHAR(7);
                SET action_var = get_action_from_update(NEW.deleted_at, OLD.deleted_at, NEW.updated_at, OLD.updated_at);
                INSERT INTO equipment_types_hist(`user`, `action`, record_id, name, in_detector, project, `system`, id_regex, params, active, description, created_at)
                VALUES (NEW.user, action_var, NEW.id, NEW.name, NEW.in_detector, NEW.project, NEW.system, NEW.id_regex, NEW.params, NEW.active, NEW.description, NEW.updated_at);
            END;
        ");
    }

    protected function downTriggers()
    {
        $this->dropTriggers();
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
}
