<?php

namespace App\Validation;

class StringRules{

    /**
     * Checks if the value matches the regex
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params The params array must have the following keys:
     * - regex: string, the regex to match
     * - errorFieldName: field Name to use on the Regex error message
     * @return bool True if the value matches the regex
     */
    public static function regexMatch(string $fieldName, array $data, string &$error, array $params): bool
    {
        $value = $data[$fieldName];
        $regex = $params['regex'];
        if (!preg_match($regex, $value)) {
            $error = sprintf(
                '%s is not valid (%s)',
                $params['errorFieldname']?? $fieldName,
                $data[$fieldName]?? null
            );
            return false;
        }
        return true;
    }
    
    /**
     * Checks if the value is has the defined length
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Maximum length of the string
     * The params array must have the following keys:
     * length: int, the exact length the value must have
     * @return bool True if the value has the defined length
     */
    public static function exactLength(string $fieldName, array $data, string &$error, array $params): bool
    {
        $value = $data[$fieldName];
        $length = $params['length'];
        if (strlen($value) != $length) {
            $error = sprintf(
                '%s must have exactly %d characters in length.',
                $fieldName,
                $length
            );
            return false;
        }
        return true;
    }

    /**
     * Checks if the value length is less than or equal to the defined length
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Maximum length of the string
     * The params array must have the following keys:
     * 
     * - length: int, the maximum length the value must have
     * 
     * @return bool True if the value length is less than or equal to the defined length
     * @see Validator::validate
     */
    public static function maxLength(string $fieldName, array $data, string &$error, array $params): bool
    {
        $value = $data[$fieldName];
        $length = $params['length'];
        if (strlen($value) > $length) {
            $error = sprintf(
                '%s must have at maximum %d characters in length.',
                $fieldName,
                $length
            );
            return false;
        }
        return true;
    }

    /**
     * Checks if the value length is greater than or equal to the defined length
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Minimum length of the string
     * The params array must have the following keys:
     * 
     * - length: int, the minimum length the value must have
     * 
     * @return bool True if the value length is greater than or equal to the defined length
     * @see Validator::validate
     */
    public static function minLength(string $fieldName, array $data, string &$error, array $params): bool
    {
        $value = $data[$fieldName];
        $length = $params['length'];
        if (strlen($value) < $length) {
            $error = sprintf(
                '%s must have at minimum %d characters in length.',
                $fieldName,
                $length
            );
            return false;
        }
        return true;
    }

    /**
     * Checks if the string starts with the defined prefix
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params The params array must have the following keys:
     * 
     * - prefix: string, the prefix the value must start with
     * 
     * @return bool True if the string starts with the defined prefix
     * @see Validator::validate
     */
    public static function startsWith(string $fieldName, array $data, string &$error, array $params): bool
    {
        $value = $data[$fieldName];
        $prefix = $params['prefix'];
        if (strpos($value, $prefix) !== 0) {
            $error = sprintf(
                '%s must start with %s.',
                $fieldName,
                $prefix
            );
            return false;
        }
        return true;
    }
}