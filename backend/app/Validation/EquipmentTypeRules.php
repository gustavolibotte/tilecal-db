<?php

namespace App\Validation;

class EquipmentTypeRules
{
    // Updated to 100 characters to support longer names like "Active HV Divider (Long Cable)"
    public static $maxNameLength = 100;
    
    // Regex updated to allow parentheses () and hyphens -
    public static $baseNameRegex = '/^[A-Za-z0-9 \(\)\-]+$/';
    
    public static $typeSlotIdRegex = '/^[0-9]+$/';
    public static $slotNameRegex = '/^[A-Za-z0-9_]+$/';

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
            max_length($data['type_name'], self::$maxNameLength, $fieldName, $error) &&
            regex_match($data['type_name'], self::$baseNameRegex, $fieldName, $error)
        );
    }

    public static function slotNameIsSanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        return (
            StringRules::regexMatch($fieldName, $data, $error, [
                'regex' => self::$slotNameRegex,
                'errorFieldname' => 'Slot name '
            ])
        );
    }
}