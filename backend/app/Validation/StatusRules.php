<?php

namespace App\Validation;

class StatusRules{

    public static string $statusIdRegex = '/^[0-9]+$/';
    public static $maxNameLength = 30;
    public static $nameRegex = '/^[A-Za-z0-9]+$/';

    /**
     * Checks if the id value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function status_id_is_sanitized($value, ?string &$error): bool
    {
        helper('string_validation');
        $fieldName = 'Status id';
        return (
            regex_match($value, Self::$statusIdRegex, $fieldName, $error)
        );
    }

    /**
     * Checks if the name value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function status_name_is_sanitized($value, ?string &$error): bool
    {
        helper('string_validation');
        $fieldName = 'Status name';
        return (
            max_length($value, Self::$maxNameLength, $fieldName, $error) &&
            regex_match($value, Self::$nameRegex, $fieldName, $error)
        );
    }
}