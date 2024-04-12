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
     * @param string &$error Error message to return if the check fails
     * @param array $params Regex to match
     */
    public static function regexMatch(string $fieldName, array $data, string &$error, array $params): bool
    {
        $value = $data[$fieldName];
        $regex = $params[0];
        if (!preg_match($regex, $value)) {
            $error = sprintf(
                '%s is not valid',
                $fieldName
            );
            return false;
        }
        return true;
    }
}