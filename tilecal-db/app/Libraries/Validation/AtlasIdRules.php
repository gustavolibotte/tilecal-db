<?php

namespace App\Library\Validation;
use App\Entities\AtlasId;

class AtlasIdRules {

    public static array $validSystems = [
        'LB' => 'Barrel',
        'LE' => 'Extended',
        'LL' => 'Optics',
        'LO' => 'Electronics',
        'LC' => 'Control'
    ];
    public static array $validProjects = [
        '20' => 'ATLAS Detector'
    ];

    public static string $sequentialNumberRegex = '^[A-Z0-9]{7}';

    public function id_has_valid_system($value): bool
    {
        $id = new AtlasId(['id' => $value]);
        return in_array($id->system, array_keys(AtlasIdRules::$validSystems));
    }

    public function id_is_system($value, string $params, array $data, ?string &$error): bool
    {   
        $correctSystem = $params;
        $id = new AtlasId(['id' => $value]);
        return $id->system == $correctSystem;
    }

    public function id_is_project($value, string $params, array $data, ?string &$error): bool
    {
        $correctProject = $params;
        $id = new AtlasId(['id' => $value]);
        return $id->project == $correctProject;
    }

    public function id_has_valid_institute($value, string $params, array $data, ?string &$error): bool
    {   
        $tableColumn = explode('.', $params);
        $table = $tableColumn[0];
        $column = $tableColumn[1];
        $id = new AtlasId(['id' => $value]);
        $db = db_connect();
        $queryText = sprintf("SELECT * FROM `%s` WHERE `%s` = '%s' LIMIT 1", $table, $column, $id->institute);
        $query = $db->query($queryText);
        $results = $query->getFirstRow();
        return isset($results);
    }
}
