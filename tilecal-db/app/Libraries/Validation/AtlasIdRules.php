<?php

namespace App\Library\Validation;
use App\Entities\AtlasId;

class AtlasIdRules {

    /**
     * Key value pairs for valid systems for the tilecal according to the ATLAS ID rules
     * Keys are abbreviations and values which system they represent
     * @var array $validSystems
     */
    public static array $validSystems = [
        'LB' => 'Barrel',
        'LE' => 'Extended',
        'LL' => 'Optics',
        'LO' => 'Electronics',
        'LC' => 'Control'
    ];

    /**
     * Key value pairs for valid projects for the database according to the ATLAS ID rules
     * Keys are abbreviations and values which system they represent
     * Since Tilecal is ALTAS, only the ATLAS detetctor code is valid
     * @var array $validProjects
     */
    public static array $validProjects = [
        '20' => 'ATLAS Detector'
    ];
    
    /**
     * Regex rule for the id sequential number part
     * @var string $sequentialNumberRegex
     */
    public static string $sequentialNumberRegex = '^[A-Z0-9]{7}';

    /**
     * Checks if the equipment id is has one of the valid systems
     *
     * @param $value  Value given by CodeIgniter validator, considered as an equipment id
     * 
     * @return bool True if the id has a valid system
     */ 
    public function id_has_valid_system($value): bool
    {
        $id = new AtlasId(['id' => $value]);
        return in_array($id->system, array_keys(AtlasIdRules::$validSystems));
    }

    /**
     * Checks if the equipment id is is for a specific system
     *
     * @param $value  Value given by CodeIgniter validator, considered as an equipment id
     * @param string $params System code to check
     * @param array $data Ignored, here for compatibility reasons
     * @param ?string &$error Ignored, here for compatibility reasons
     * 
     * @return bool True if the id for the specified system
     */ 
    public function id_is_system($value, string $params, array $data, ?string &$error): bool
    {   
        $correctSystem = $params;
        $id = new AtlasId(['id' => $value]);
        return $id->system == $correctSystem;
    }

    /**
     * Checks if the equipment id is is for a specific project
     *
     * @param $value  Value given by CodeIgniter validator, considered as an equipment id
     * @param string $params Project code to check
     * @param array $data Ignored, here for compatibility reasons
     * @param ?string &$error Ignored, here for compatibility reasons
     * 
     * @return bool True if the id is for the specified project
     */ 
    public function id_is_project($value, string $params, array $data, ?string &$error): bool
    {
        $correctProject = $params;
        $id = new AtlasId(['id' => $value]);
        return $id->project == $correctProject;
    }

    /**
     * Checks if the equipment id is has a valid institute
     *
     * @param $value  Value given by CodeIgniter validator, considered as an equipment id
     * @param string $params String with format <table>.<column> that refers to the database column that has the valid institute mnes
     * @param array $data Ignored, here for compatibility reasons
     * @param ?string &$error Ignored, here for compatibility reasons
     * 
     * @return bool True if the id has a valid institute
     */ 
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
