<?php

namespace App\Validation;

class StringRules{

    /**
     * Checks if the string ends with a specific substring
     *
     * @param $value  Value given by CodeIgniter validator
     * @param string $params Substring to check
     * @param array $data Ignored, it exists for compatibility reasons only
     * @param ?string &$error Ignored, it exists for compatibility reasons only
     * 
     * @return bool True if the value ends with the substring in $params
     */ 
    public function string_ends_with($value, string $params, array $data, ?string &$error): bool
    {   
        return str_ends_with($value, $params);
    }

    /**
     * Checks if the string starts with a specific substring
     *
     * @param $value  Value given by CodeIgniter validator
     * @param string $params Substring to check
     * @param array $data Ignored, it exists for compatibility reasons only
     * @param ?string &$error Ignored, it exists for compatibility reasons only
     * 
     * @return bool True if the value starts with the substring in $params
     */ 
    public function string_starts_with($value, string $params, array $data, ?string &$error): bool
    {   
        return str_starts_with($value, $params);
    }

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
     * length: int, the maximum length the value must have
     * @return bool True if the value length is less than or equal to the defined length
     */
    public static function maxLength(string $fieldName, array $data, string &$error, array $params): bool
    {
        $value = $data[$fieldName];
        $length = $params['length'];
        if (strlen($value) > $length) {
            $error = sprintf(
                '%s must have exactly %d characters in length.',
                $fieldName,
                $length
            );
            return false;
        }
        return true;
    }
}