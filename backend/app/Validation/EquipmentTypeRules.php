<?php

namespace App\Validation;

class EquipmentTypeRules{

    public static $maxNameLength = 30;
    public static $baseNameRegex = '/^[A-Za-z0-9 ]+$/';
    public static $typeSlotIdRegex = '/^[0-9]+$/';

    /**
     * Checks if the name value is sanitized
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Ignored
     */
    public static function nameIsSanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        helper('string_validation');
        $fieldName = 'Equipment type name';
        return (
            max_length($data['type_name'], Self::$maxNameLength, $fieldName, $error) &&
            regex_match($data['type_name'], Self::$baseNameRegex, $fieldName, $error)
        );
    }

    /**
     * Checks if the type_slot_name value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public static function equipment_type_slot_name_is_sanitized($value, ?string &$error): bool
    {
        helper('string_validation');
        $fieldName = 'Equipment type slot name';
        return (
            regex_match($value, '/^[A-Za-z0-9_]+$/', $fieldName, $error)
        );
    }
}