<?php

namespace App\Validation;
use App\Entities\AtlasId;
use App\Models\EquipmentModel;

class EquipmentRules{


    public static int $serialMaxSize = 36;
    public static string $serialRegex = '/^[A-Za-z0-9 -_]*$/';
    public static int $commentsMaxSize = 65535; //MySQL max text size
    public static string $commentsRegex = '/^.*$/';
    public static int $atlasIdLength = 14;
    /**
     * Key value pairs for valid systems for the tilecal according to the ATLAS ID rules
     * Keys are abbreviations and values which system they represent
     * @var array $validSystems
     */
    public static array $validSystems = [
        'LB' => 'Barrel',
        'LE' => 'Extended',
        'LL' => 'Electronics',
        'LO' => 'Optics',
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
    public static string $sequentialNumberRegex = '/^[A-Z0-9]+$/';

    /**
     * Sequential number length
     * @var string $sequentialNumberLength
     */
    public static int $sequentialNumberLength = 7;

    /**
     * Checks if the id value is sanitized
     * If the id is a 14 character code,
     * it is considered an AtlasId and the sequential number is extracted
     * The value is changed to the sequential number inplace.
     * Otherwise, supposes that the value is the sequential number
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function equipment_id_is_sanitized(&$value, ?string &$error): bool
    {
        helper('string_validation');
        $fieldName = 'Equipment id';
        if (strlen($value) === Self::$sequentialNumberLength){
            return regex_match($value, Self::$sequentialNumberRegex, $fieldName, $error);
        } else if (strlen($value) === Self::$atlasIdLength){
            $atlasId = new AtlasId(['id' => $value]);
            $value = $atlasId->sequential;
            return regex_match($value, Self::$sequentialNumberRegex, $fieldName, $error);
        } else {
            $error = "The equipment id must be a 14 character ATLAS ID or a 7 character ATLAS sequential number";
            return false;
        }

    }

    /**
     * Checks if the serial value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function equipment_serial_is_sanitized($value, ?string &$error): bool
    {
        helper('string_validation');
        $fieldName = 'Equipment serial';
        return (
            max_length($value, Self::$serialMaxSize, $fieldName, $error) &&
            regex_match($value, Self::$serialRegex, $fieldName, $error)
        );
    }

    /**
     * Checks if the serial value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function equipment_comments_is_sanitized($value, ?string &$error): bool
    {
        helper('string_validation');
        $fieldName = 'Equipment comments';
        return (
            max_length($value, Self::$commentsMaxSize, $fieldName, $error) &&
            regex_match($value, Self::$commentsRegex, $fieldName, $error)
        );
    }

    /**
     * Checks if the a value doesn't exist for a given type. A negation of is_type_not_unique
     *
     * @param $value  Value given by CodeIgniter validator
     * @param string $params Params with the format <column>,<typeid>. column is the equipments column to check for the given type id
     * @param array $data Ignored
     * @param ?string &$error Ignored
     * 
     * @return bool True if the data is unique on that column for a given equipment type
     */ 
    public function is_type_unique($value, string $params, array $data, ?string &$error): bool
    {   
        return !$this->is_type_not_unique($value, $params, $data, $error);
    }

    /**
     * Checks if the a value already exists for a given type
     *
     * @param $value  Value given by CodeIgniter validator
     * @param string $params Params with the format <column>,<typeid>. column is the equipments column to check for the given type id
     * @param array $data Ignored
     * @param ?string &$error Ignored
     * 
     * @return bool True if the data is not unique on that column for a given equipment type
     */ 
    public function is_type_not_unique($value, string $params, array $data, ?string &$error): bool
    {
        $params = explode(',', $params);
        $column = $params[0];
        $typeId = $params[1];
        $db = db_connect();
        $queryText = sprintf("SELECT {$column} FROM `equipments` WHERE `{$column}` = '{$value}' AND type_id = %d LIMIT 1", $typeId);
        $query = $db->query($queryText);
        $results = $query->getFirstRow();
        return isset($results);
    }

    /**
     * Checks if the equipment id is of a given type
     *
     * @param $value  Value given by CodeIgniter validator, considered as an equipment id
     * @param string $params Equipment type name
     * @param array $data Ignored
     * @param ?string &$error Error string in the format "The parent id must be from a %s. The given id is from a %s"
     * 
     * @return bool True if the data was approved on this rule 
     */ 
    public function equipment_is_type($value, string $params, array $data, ?string &$error): bool
    {   
        helper('string');
        $equipmentModel = new EquipmentModel();
        $equipment = $equipmentModel->fullQuery()->find($value);
        $approved = $equipment->type_name == $params;
        if (!$approved){
            $error = sprintf(
                "The parent id must be from a %s. The given id is from a %s",
                snake2Capitalized($params),
                $equipment->type_name
            );
        }
        return $approved;
    }

    /**
     * Checks if the equipment id is of a given type
     *
     * @param $value  Value given by CodeIgniter validator, considered as an equipment id
     * @param string $params Ignored
     * @param array $data Ignored
     * @param ?string &$error Error string. Formated automatically by the function.
     * 
     * @return bool True if the data was approved on this rule
     */ 
    public function equip_is_not_assembled_into($value, ?string &$error = null): bool
    {
        $childId = $value;
        $relation = model('ParentChildModel')
            ->where('child_equip_id', $childId)
            ->first();
        if (isset($relation)){
            $error = sprintf(
                'The equipment %s is already assembled into equipment %s.',
                $relation->child_equip_id,
                $relation->parent_equip_id
            );
            return false;
        }
        return true;
    }
}