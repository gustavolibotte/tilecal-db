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
     * Since Tilecal is ALTAS, only the ATLAS detector code is valid
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
     * Valid values for PMT models
     * @var array $validPmtModelValues
     */
    public static array $validPmtModelValues = [
        'R11187',
        'R7877',
        'R7787',
        'R11187-SEL',
        'R11187-A1',
        'R7600-20',
        'R5900MOD'
    ];

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
        if (!StringRules::regexMatch($fieldName, $data, $error, ['regex' => self::$sequentialNumberRegex])) {
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
    public static function serialIsSanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        $approved = (
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
    public static function equipmentCommentsIsSanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        $approved = (
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
    public static function isTypeUnique(string $fieldName, array $data, string &$error, array $params): bool
    {
        if (self::isTypeNotUnique($fieldName, $data, $error, $params)) {
            $error = sprintf(
                '%s %s exists for the selected type.',
                ucfirst($fieldName),
                $data[$fieldName]
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
    public static function isTypeNotUnique(string $fieldName, array $data, string &$error, array $params): bool
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
                ucfirst($fieldName),
                $value
            );
            return false;
        }
        return true;
    }

    public static function processSlotError(string $error, int $slotIdx){
        return sprintf(
            'Slot %d: %s',
            $slotIdx,
            $error
        );
    }

    /**
     * Checks if the slots are valid for assemble
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Parameters for the check
     */
    public static function validAssemblySlots(string $fieldName, array &$data, string &$error, array $params): bool
    {
        $parentId = $data[$params['parent_id_field']];
        foreach ($data[$fieldName] as $index => &$assemblySlot) {
            if (!GeneralRules::required('name', $assemblySlot, $error, [])) {
                $error = self::processSlotError($error, $index);
                return false;
            }
            if (!GeneralRules::required('equipment_id', $assemblySlot, $error, [])) {
                $error = self::processSlotError($error, $index);
                return false;
            }

            if (!self::idIsSanitized('equipment_id', $assemblySlot, $error, [])) {
                $error = self::processSlotError($error, $index);
                return false;
            }
            if (!EquipmentTypeRules::slotNameIsSanitized('name', $assemblySlot, $error, [])) {
                $error = self::processSlotError($error, $index);
                return false;
            }

            if (!DatabaseRules::isNotUnique('equipment_id', $assemblySlot, $error, ['table' => 'equipments', 'column' => 'id'])) {
                $error = self::processSlotError($error, $index);
                return false;
            }

            if (!DatabaseRules::isNotUnique('name', $assemblySlot, $error, ['table' => 'type_slots', 'column' => 'name'])) {
                $error = self::processSlotError($error, $index);
                return false;
            }

            $equipmentModel = model('EquipmentModel');
            if ($equipmentModel->equipIsAssembledInto($assemblySlot['equipment_id'])) {
                $error = sprintf(
                    '%s: The equipment %s is already assembled into another one.',
                    $assemblySlot['name'],
                    $assemblySlot['equipment_id']
                );
                $error = self::processSlotError($error, $index);
                return false;
            }
    
            if (!$equipmentModel->equipHasSlot($parentId, $assemblySlot['name'])) {
                $error = sprintf(
                    '%s: Equipment %s doesn\'t have the selected slot',
                    $assemblySlot['name'],
                    $parentId
                );
                $error = self::processSlotError($error, $index);
                return false;
            }
    
            if (!$equipmentModel->equipSlotIsEmpty($parentId, $assemblySlot['name'])) {
                $error = sprintf(
                    '%s: Selected slot from equipment %s is in use.',
                    $assemblySlot['name'],
                    $parentId
                );
                $error = self::processSlotError($error, $index);
                return false;
            }
    
            if (!model('EquipmentTypeModel')->slotAllowsEquip($assemblySlot['name'], $assemblySlot['equipment_id'])) {
                $error = sprintf(
                    '%s: Slot is available but does not allow insertion of equipment %s.',
                    $assemblySlot['name'],
                    $assemblySlot['equipment_id']
                );
                $error = self::processSlotError($error, $index);
                return false;
            }

        }
        return true;
    }

    /**
     * Returns the rules for registering a new equipment based on its type.
     * Useful for equipment types that have custom rules for registering.
     * @param string $typeName Name of the equipment type
     * @return array Rules for registering a new equipment
     * @see Validator::validate
     */
    public static function getRegisterRulesFromTypeName(string $typeName = ''): array
    {
        helper('string');
        $specificRuleMethod = sprintf(
            'get%sRegisterRule',
            makeCamelCase($typeName)
        );
        if (method_exists(self::class, $specificRuleMethod)) {
            $rules = self::$specificRuleMethod();
        } else {
            $rules = self::getDefaultRegisterRule();
        }
        return $rules;
    }

    /**
     * Returns the rules for editing an equipment based on its id.
     * @param string $id Id of the equipment to edit
     * @return array Rules for editing a new equipment
     * @see Validator::validate
     */
    public static function getEditRulesFromId(string $id = ''): array
    {
        helper('string');
        $equip = model('EquipmentModel')
            ->join('equipment_types', 'equipments.type_id = equipment_types.id', 'left')
            ->select('equipment_types.name as type_name')->find($id);
        if (empty($equip->type_name)) {
            throw new \Exception('Equipment not found for building the edit rules.');
        }
        $specificRuleMethod = sprintf(
            'get%sEditRule',
            makeCamelCase($equip->type_name)
        );
        if (method_exists(self::class, $specificRuleMethod)) {
            $rules = self::$specificRuleMethod();
        } else {
            $rules = self::getDefaultEditRule();
        }
        return $rules;
    }

    /**
     * Returns the default rules for registering a new equipment.
     * @return array Default rules for registering a new equipment
     * @see Validator::validate
     */
    public static function getDefaultRegisterRule(): array
    {
        return [
            'type_name' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\EquipmentTypeRules::nameIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'equipment_types',
                        'column' => 'name'
                    ]
                ]
            ],
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
                'permitEmpty' => [],
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
                'permitEmpty' => [],
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

    /**
     * Returns the default rules for editing a new equipment.
     * The rules consider that the field 'id' has been checked previously
     * @return array Default rules for editing a new equipment
     * @see Validator::validate
     */
    public static function getDefaultEditRule(): array
    {
        return [
            'id' => [],
            'owner_mne' => [
                'removeEmpty' => [],
                '\App\Validation\InstituteRules::mneIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'institutes',
                        'column' => 'mne'
                    ]
                ]
            ],
            'detector_location_name' => [
                'removeEmpty' => [],
                '\App\Validation\DetectorLocationRules::nameIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'detector_locations',
                        'column' => 'name'
                    ]
                ]
            ],
            'location_name' => [
                'removeEmpty' => [],
                '\App\Validation\LocationRules::nameIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'locations',
                        'column' => 'name'
                    ]
                ]
            ],
            'status_name' => [
                'removeEmpty' => [],
                '\App\Validation\StatusRules::nameIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'statuses',
                        'column' => 'name'
                    ]
                ]
            ],
            'serial' => [
                'removeEmpty' => [],
                '\App\Validation\EquipmentRules::serialIsSanitized' => [],
                '\App\Validation\DatabaseRules::isUnique' => [
                    'params' => [
                        'table' => 'equipments',
                        'column' => 'serial'
                    ]
                ]
            ],
            'comments' => [
                'removeEmpty' => [],
                '\App\Validation\EquipmentRules::commentsIsSanitized' => []
            ]
        ];
    }

    /**
     * Returns the rules for registering a PMT.
     * @return array Rules for registering a PMT
     * @see Validator::validate
     */
    public static function getPmtRegisterRule(): array
    {
        return [
            'type_name' => [
                '\App\Validation\GeneralRules::isStrictlyEqual' => [
                    'value' => 'PMT'
                ],
            ],
            'serial' => [
                '\App\Validation\GeneralRules::required' => [],
                '\App\Validation\EquipmentRules::serialIsSanitized' => [],
                '\App\Validation\DatabaseRules::isUnique' => [
                    'params' => [
                        'table' => 'equipments',
                        'column' => 'serial'
                    ]
                    ],
                '\App\Validation\EquipmentRules::buildPmtId' => []
            ],
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
            'comments' => [
                'permitEmpty' => [],
                '\App\Validation\EquipmentRules::commentsIsSanitized' => []
            ],
            'beta' => [
                'permitEmpty' => [],
                '\App\Validation\GeneralRules::isFloat' => [
                    'cast' => true
                ],
                '\App\Validation\GeneralRules::greaterThan' => [
                    'value' => 0
                ]
            ],
            'nominal_hv' => [
                'permitEmpty' => [],
                '\App\Validation\GeneralRules::isFloat' => [
                    'cast' => true
                ],
                '\App\Validation\GeneralRules::greaterThan' => [
                    'value' => 0
                ]
                ],
            'qe' => [
                'permitEmpty' => [],
                '\App\Validation\GeneralRules::isFloat' => [
                    'cast' => true
                ],
                '\App\Validation\GeneralRules::greaterThan' => [
                    'value' => 0
                ]
            ],
            'model' => [
                'permitEmpty' => [],
                '\App\Validation\GeneralRules::inList' => [
                    'list' => self::$validPmtModelValues
                ]
                ],
            'phase1_position' => [
                'permitEmpty' => [],
                '\App\Validation\GeneralRules::isInt' => [
                    'cast' => true
                ],
                '\App\Validation\GeneralRules::greaterThan' => [
                    'value' => 0
                ],
                '\App\Validation\GeneralRules::smallerThan' => [
                    'value' => 50
                ],
            ]
        ];
    }

    /**
     * Returns the rules for editing a PMT.
     * @return array Rules for editing a PMT
     * @see Validator::validate
     */
    public static function getPmtEditRule(): array
    {
        return [
            'id' => [],
            'owner_mne' => [
                'removeEmpty' => [],
                '\App\Validation\InstituteRules::mneIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'institutes',
                        'column' => 'mne'
                    ]
                ]
            ],
            'detector_location_name' => [
                'removeEmpty' => [],
                '\App\Validation\DetectorLocationRules::nameIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'detector_locations',
                        'column' => 'name'
                    ]
                ]
            ],
            'location_name' => [
                'removeEmpty' => [],
                '\App\Validation\LocationRules::nameIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'locations',
                        'column' => 'name'
                    ]
                ]
            ],
            'status_name' => [
                'removeEmpty' => [],
                '\App\Validation\StatusRules::nameIsSanitized' => [],
                '\App\Validation\DatabaseRules::isNotUnique' => [
                    'params' => [
                        'table' => 'statuses',
                        'column' => 'name'
                    ]
                ]
            ],
            'comments' => [
                'removeEmpty' => [],
                '\App\Validation\EquipmentRules::commentsIsSanitized' => []
            ],
            'beta' => [
                'permitEmpty' => [],
                '\App\Validation\GeneralRules::isFloat' => [
                    'cast' => true
                ],
                '\App\Validation\GeneralRules::greaterThan' => [
                    'value' => 0
                ]
            ],
            'nominal_hv' => [
                'permitEmpty' => [],
                '\App\Validation\GeneralRules::isFloat' => [
                    'cast' => true
                ],
                '\App\Validation\GeneralRules::greaterThan' => [
                    'value' => 0
                ]
                ],
            'qe' => [
                'permitEmpty' => [],
                '\App\Validation\GeneralRules::isFloat' => [
                    'cast' => true
                ],
                '\App\Validation\GeneralRules::greaterThan' => [
                    'value' => 0
                ]
            ],
            'model' => [
                'permitEmpty' => [],
                '\App\Validation\GeneralRules::inList' => [
                    'list' => self::$validPmtModelValues
                ]
                ],
            'phase1_position' => [
                'permitEmpty' => [],
                '\App\Validation\GeneralRules::isInt' => [
                    'cast' => true
                ],
                '\App\Validation\GeneralRules::greaterThan' => [
                    'value' => 0
                ],
                '\App\Validation\GeneralRules::smallerThan' => [
                    'value' => 50
                ],
            ]
        ];
    }

    public static function buildPmtId(string $fieldName, array &$data, string &$error, array $params): bool
    {
        $data['id'] = sprintf(
            '0%s',
            $data['serial']);
        return true;
    }
}