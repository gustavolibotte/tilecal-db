<?php

namespace App\Validation;

class LocationRules{

    public static $idRegex = '/^[0-9]+$/';
    public static $maxNameLength = 100;
    public static $nameRegex = '/^[A-Za-z0-9 ]+$/';

    /**
     * Checks if the id value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function location_id_is_sanitized($value, ?string &$error): bool
    {
        helper('string_validation');
        $fieldName = 'Location Id';
        return (
            regex_match($value, Self::$idRegex, $fieldName, $error)
        );
    }

    /**
     * Checks if the name value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function location_name_is_sanitized($value, ?string &$error): bool
    {
        helper('string_validation');
        $fieldName = 'Location name';
        return (
            max_length($value, Self::$maxNameLength, $fieldName, $error) &&
            regex_match($value, Self::$nameRegex, $fieldName, $error)
        );
    }
}