<?php

namespace App\Validation;

use App\Entities\AtlasId;
use App\Models\EquipmentModel;

class EquipmentRules
{
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
     * If the id is a 14 character code, it is considered an ATLAS ID.
     * The sequential number is extracted and checked against the regex
     * If the id is a 7 character sequential number, it is checked against the regex
     * If the id has the string 'CHI10V', it is considered a mainboard produced by Chicago
     * The sequential number is extracted and checked against the regex.
     * This is needed because the id from Chicago mainboards do not have the same format as the ATLAS ID
     * @param string $fieldName Field name with the id value
     * @param array $data Data array to be modified
     * @param string $error Error string to be modified
     * @param array $params Ignored
     */
    public static function idIsSanitized(string $fieldName, array &$data, string &$error, array $params): bool
    {
        helper('string_validation');
        $id = $data[$fieldName];
        $strLength = strlen($id);
        if ($strLength === self::$sequentialNumberLength) {
            // Id is a sequential number
        } else if ($strLength === self::$atlasIdLength) {
            // Id is an ATLAS ID
            $atlasId = new AtlasId(['id' => $id]);
            $id = $atlasId->sequential;
        } else if (str_contains($id, 'CHI10V')) {
            // Id is a mainboard produced by Chicago
            $id = str_replace('V', '', $id);
            $atlasId = new AtlasId(['id' => $id]);
            $id = $atlasId->sequential;
        } else {
            $error = "Invalid equipment id";
            return false;
        }
        if (!regex_match($id, self::$sequentialNumberRegex, 'Equipment id', $error)) {
            return false;
        }
        $data[$fieldName] = $id;
        return true;
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
            max_length($value, self::$serialMaxSize, $fieldName, $error) &&
            regex_match($value, self::$serialRegex, $fieldName, $error)
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
            max_length($value, self::$commentsMaxSize, $fieldName, $error) &&
            regex_match($value, self::$commentsRegex, $fieldName, $error)
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
        if (!$approved) {
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
        if (isset($relation)) {
            $error = sprintf(
                'The equipment %s is already assembled into equipment %s.',
                $relation->child_equip_id,
                $relation->parent_equip_id
            );
            return false;
        }
        return true;
    }

    public function is_valid_slot_for_assembly($value, string $params, array $data, ?string &$error): bool
    {
        if (!property_exists($value, 'name')) {
            $error = 'Missing slot name.';
            return false;
        }
        if (!property_exists($value, 'equipment_id')) {
            $error = 'Missing slot equipment id to assemble.';
            return false;
        }
        $slotName = $value->name;
        $childId = $value->equipment_id;
        $parentId = $params;
        $equipmentModel = model('EquipmentModel');

        if (!self::equipment_id_is_sanitized($childId, $error)) {
            $error = sprintf(
                'Invalid equipment id %s.',
                $childId
            );
            return false;
        }

        if (!self::equipment_id_is_sanitized($parentId, $error)) {
            $error = sprintf(
                'Invalid equipment id %s.',
                $parentId
            );
            return false;
        }

        if (!EquipmentTypeRules::equipment_type_slot_name_is_sanitized($slotName, $error)) {
            $error = sprintf(
                'Invalid slot name %s.',
                $slotName
            );
            return false;
        }

        helper('equipment');
        $childId = preprocessEquipmentId($childId);
        $parentId = preprocessEquipmentId($parentId);
        $equipmentModel = model('EquipmentModel');
        $equip = $equipmentModel->select('1')->find($childId);
        if (!isset($equip)) {
            $error = sprintf(
                '%s: Equipment %s does not exist.',
                $slotName,
                $childId
            );
            return false;
        }

        if ($equipmentModel->equipIsAssembledInto($childId)) {
            $error = sprintf(
                '%s: The equipment %s is already assembled into another one.',
                $slotName,
                $childId
            );
            return false;
        }

        if (!$equipmentModel->equipHasSlot($parentId, $slotName)) {
            $error = sprintf(
                '%s: Equipment %s doesn\'t have the selected slot',
                $slotName,
                $parentId
            );
            return false;
        }

        if (!$equipmentModel->equipSlotIsEmpty($parentId, $slotName)) {
            $error = sprintf(
                '%s: Selected slot from equipment %s is in use.',
                $slotName,
                $parentId
            );
            return false;
        }

        if (!model('EquipmentTypeModel')->slotAllowsEquip($slotName, $childId)) {
            $error = sprintf(
                '%s: Slot is available but does not allow insertion of equipment %s.',
                $slotName,
                $childId
            );
            return false;
        }

        return true;
    }
}