<?php

namespace App\Validation;

class GeneralRules	{

    /**
     * Checks if the value exists in the data array and is not empty
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Ignored
     */
    public static function required(string $fieldName, array $data, string &$error, array $params): bool
    {
        if (!isset($data[$fieldName]) || empty($data[$fieldName])){
            $error = sprintf(
                '%s is required',
                $fieldName
            );
            return false;
        }
        return true;
    }

    /**
     * Checks if the value exists in the data array and is an array
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Ignored
     */
    public static function isArray(string $fieldName, array $data, string &$error, array $params): bool
    {
        if (!is_array($data[$fieldName])){
            $error = sprintf(
                '%s must be an array',
                $fieldName
            );
            return false;
        }
        return true;
    }
}