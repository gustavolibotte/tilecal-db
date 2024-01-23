<?php

namespace App\Database\Migrations;

use App\Library\BaseMigration;

class CaseSensitiveCollation extends BaseMigration
{
    protected $dbconnect;

    public function up()
    {
        $this->dbconnect = db_connect();
        $this->dbconnect->transException(true)->transBegin();
        $this->dbconnect->query("SET FOREIGN_KEY_CHECKS=0;");

        $query = sprintf(
            "ALTER DATABASE %s CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_as_cs;",
            $this->dbconnect->getDatabase()
        );
        $this->dbconnect->query($query);
        $histedTables = [
            'allowed_type_slots',
            'detector_locations',
            'equipment_types',
            'equipments',
            'equipments_to_files',
            'files',
            'institutes',
            'locations',
            'parent_child_equips',
            'statuses',
            'type_slots'
        ];
        foreach($histedTables as $tableName){
            $this->collateWithhistory($tableName);
        }

        $simpleTables = [
            'ci_sessions',
            'features'
        ];
        foreach($simpleTables as $tableName){
            $this->collate($tableName);
        }
        $this->dbconnect->query("SET FOREIGN_KEY_CHECKS=1;");
        $this->dbconnect->transCommit();
    }

    public function down()
    {
        //
    }

    protected function collate(string $tableName)
    {
        $baseQuery = "ALTER TABLE %s CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_as_cs;";
        $this->dbconnect->query(sprintf($baseQuery, $tableName));
    }

    protected function collateWithhistory(string $tableName)
    {
        $this->collate($tableName . '_hist');
        $this->collate($tableName);
    }
}
