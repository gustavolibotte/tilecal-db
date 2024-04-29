<?php

namespace App\Validation;

use App\Entities\AtlasId;

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
     * @return bool True if the id is sanitized
     * @see StringRules::regexMatch
     */
    public static function idIsSanitized(string $fieldName, array &$data, string &$error, array $params): bool
    {
        helper('string_validation');
        $id = $data[$fieldName];
        $strLength = strlen($id);
        $error = "Equipment ID did not match any known format.";
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
            return false;
        }
        if (!StringRules::regexMatch($fieldName, ['id' => $id], $error, ['regex' => self::$sequentialNumberRegex])) {
            return false;
        }
        $data[$fieldName] = $id;
        return true;
    }

    /**
     * Checks if the equipment serial value is sanitized
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Ignored
     * @return bool True if the value is sanitized
     * @see StringRules::maxLength
     * @see StringRules::regexMatch
     */
    public function equipmentSerialIsSanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        $approved =  (
            StringRules::maxLength($fieldName, $data, $error, ['length' => self::$serialMaxSize]) &&
            StringRules::regexMatch($fieldName, $data, $error, ['regex' => self::$serialRegex])
        );
        return $approved;
    }

    /**
     * Checks if the equipment comments value is sanitized
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Ignored
     * @return bool True if the value is sanitized
     * @see StringRules::maxLength
     */
    public function equipmentCommentsIsSanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        $approved =  (
            StringRules::maxLength($fieldName, $data, $error, ['length' => self::$commentsMaxSize])
        );
        return $approved;
    }

    /**
     * Checks if a given field has a unique value for a given equipment type
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Parameters for the check
     * The params array must have the following keys:
     * typeIdField: int, the name of the field that contains the equipment type id
     * column: string, the column to check for uniqueness
     * @return bool True if the value is unique for the selected type
     */
    public function isTypeUnique(string $fieldName, array $data, string &$error, array $params): bool
    {
        if (self::isTypeNotUnique($fieldName, $data, $error, $params)) {
            $error = sprintf(
                '%s %s exists for the selected type.',
                ucfirst($fieldName), $data[$fieldName]
            );
            return false;
        }
        return true;
    }

    /**
     * Checks if a given field has a non unique value for a given equipment type
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Parameters for the check
     * The params array must have the following keys:
     * typeIdField: int, the name of the field that contains the equipment type id
     * column: string, the column to check for uniqueness
     * @return bool True if the value is unique for the selected type
     */
    public function isTypeNotUnique(string $fieldName, array $data, string &$error, array $params): bool
    {
        $value = $data[$fieldName];
        $typeIdField = $params['typeIdField'];
        $typeId = $data[$typeIdField];
        $column = $params['column'];
        $db = db_connect();
        $queryResult = $db->table('equipments')
            ->select('1')
            ->where($column, $value)
            ->where('type_id', $typeId)
            ->get(1)->getResultArray();
        if (count($queryResult) === 0) {
            $error = sprintf(
                '%s %s doesn\'t exist for the selected type.',
                ucfirst($fieldName), $value
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

    public static function getRegisterRulesFromTypeName(string $typeName): array
    {
        helper('string');
        $specificRuleMethod = sprintf(
            'get%sRegisterRule',
            makeCamelCase($typeName)
        );
        if (method_exists(Self::class, $specificRuleMethod)) {
            $rules = Self::$specificRuleMethod();
        } else {
            $rules = Self::getDefaultRegisterRule();
        }
        return $rules;
    }

    /**
     * Returns the default rules for registering a new equipment.
     * The rules consider that the field 'type_name' has been checked previously
     * @return array Default rules for registering a new equipment
     * @see Validator::validate
     */
    public static function getDefaultRegisterRule(): array
    {
        return [
            'id' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\EquipmentRules::idIsSanitized' => [],
                '\App\Validation\DatabaseRules::isUnique' => [
                    'params' => [
                        'table' => 'equipments',
                        'column' => 'id'
                    ]
                ]
            ],
            'type_name' => [], // Should have been checked previously
            'owner_mne' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\InstituteRules::mneIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'institutes',
                        'column' => 'mne'
                    ]
                ]
            ],
            'detector_location_name' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\DetectorLocationRules::nameIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'detector_locations',
                        'column' => 'name'
                    ]
                ]
            ],
            'location_name' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\LocationRules::nameIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'locations',
                        'column' => 'name'
                    ]
                ]
            ],
            'status_name' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\StatusRules::nameIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'statuses',
                        'column' => 'name'
                    ]
                ]
            ],
            'serial' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\EquipmentRules::serialIsSanitized' => [],
                '\App\Validation\DatabaseRules::isUnique' => [
                    'params' => [
                        'table' => 'equipments',
                        'column' => 'serial'
                    ]
                ]
            ],
            'comments' => [
                'permitEmpty' => [],
                '\App\Validation\EquipmentRules::commentsIsSanitized' => []
            ]
        ];
    }
}