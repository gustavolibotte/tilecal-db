<?php

namespace App\Validation;

class EquipmentTypeRules{

    public static $maxNameLength = 30;
    public static $baseNameRegex = '/^[A-Za-z0-9 ]+$/';
    public static $typeSlotIdRegex = '/^[0-9]+$/';

    /**
     * Checks if the name value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function equipment_type_name_is_sanitized($value, ?string &$error): bool
    {
        helper('string_validation');
        $fieldName = 'Equipment type name';
        return (
            max_length($value, Self::$maxNameLength, $fieldName, $error) &&
            regex_match($value, Self::$baseNameRegex, $fieldName, $error)
        );
    }

    /**
     * Checks if the type_slot value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function equipment_type_slot_id_is_sanitized($value, ?string &$error): bool
    {

        helper('string_validation');
        $fieldName = 'Equipment type slot id';
        return (
            regex_match($value, Self::$typeSlotId, $fieldName, $error)
        );
    }

    /**
     * Checks if the type_slot_name value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function equipment_type_slot_name_is_sanitized($value, ?string &$error): bool
    {
        helper('string_validation');
        $fieldName = 'Equipment type slot name';
        return (
            regex_match($value, '/^[A-Za-z0-9 ]+$/', $fieldName, $error)
        );
    }
}