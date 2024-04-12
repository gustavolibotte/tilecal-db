<?php

namespace App\Validation;

class LocationRules{

    public static $maxNameLength = 100;
    public static $nameRegex = '/^[A-Za-z0-9 ]+$/';

    /**
     * Checks if the location name value is sanitized
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Ignored
     * @return bool True if the value is sanitized
     * @see StringRules::maxLength
     * @see StringRules::regexMatch
     */
    public function location_name_is_sanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        $approved =  (
            StringRules::maxLength($fieldName, $data, $error, ['length' => self::$maxNameLength]) &&
            StringRules::regexMatch($fieldName, $data, $error, ['regex' => self::$nameRegex])
        );
        return $approved;
    }
}