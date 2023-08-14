<?php

namespace App\Library\Validation;

class AtlasIdRules {

    public function id_is_system($value, string $params, array $data, ?string &$error): bool
    {   
        $correctSystem = $params;
        $idSystem = substr($value, 2, 2);
        return $idSystem == $correctSystem;
    }

    public function id_is_project($value, string $params, array $data, ?string &$error): bool
    {
        $correctProject = $params;
        $idProject = substr($value, 0, 2);
        return $idProject == $correctProject;
    }

    public function id_has_valid_institute($value, string $params, array $data, ?string &$error): bool
    {   
        helper('atlas_id');
        $tableColumn = explode('.', $params);
        $table = $tableColumn[0];
        $column = $tableColumn[1];
        $instituteMne = getInstituteMne($value);
        $db = db_connect();
        $queryText = sprintf("SELECT * FROM `%s` WHERE `%s` = '%s' LIMIT 1", $table, $column, $instituteMne);
        $query = $db->query($queryText);
        $results = $query->getFirstRow();
        return isset($results);
    }
}
