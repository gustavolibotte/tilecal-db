<?php

namespace App\Validation;

class StatusRules{

    public static string $statusIdRegex = '/^[0-9]+$/';
    public static $maxNameLength = 30;
    public static $nameRegex = '/^.*$/';

    /**
     * Checks if the status name value is sanitized
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Ignored
     * @return bool True if the value is sanitized
     * @see StringRules::maxLength
     * @see StringRules::regexMatch
     * @see Validator::validate
     */
    public static function nameIsSanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        $approved =  (
            StringRules::maxLength($fieldName, $data, $error, ['length' => self::$maxNameLength]) &&
            StringRules::regexMatch($fieldName, $data, $error, ['regex' => self::$nameRegex])
        );
        return $approved;
    }
}