<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixHistTriggers extends Migration
{
    public function up()
    {
        $this->dropTriggers();
        $this->upTriggers();
    }

    public function down()
    {
        $this->dropTriggers();
        $this->upTriggers();
    }

    protected function dropTriggers()
    {
        $this->db->query("
            DROP TRIGGER IF EXISTS `institutes_hist_update`;
        ");

        $this->db->query("
            DROP TRIGGER IF EXISTS `files_hist_update`;
        ");
    }

    protected function upTriggers()
    {
        $this->db->query("
            CREATE TRIGGER institutes_hist_update
            AFTER UPDATE ON institutes
            FOR EACH ROW
            BEGIN
                DECLARE action_var VARCHAR(7);
                SET action_var = get_action_from_update(NEW.deleted_at, OLD.deleted_at, NEW.updated_at, OLD.updated_at);
                INSERT INTO institutes_hist(`user`, `action`, record_id, name, mne, country, created_at)
                VALUES (NEW.user, action_var, NEW.id, NEW.name, NEW.mne, NEW.country, NEW.updated_at);
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
                VALUES (NEW.user, action_var, NEW.id, NEW.path, NEW.name, NEW.description, NEW.updated_at);
            END;
        ");
    }

    protected function downTriggers()
    {
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
}
