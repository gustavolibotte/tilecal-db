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

    /**
     * Checks if the value exists in the data array and is strictly equal to the value in the params array
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Array containing the value to compare to
     * 
     * The params array should have the following keys:
     * - `value` - the value to compare to
     * 
     * @return bool
     * @see Validator::validate
     */
    public static function isStrictlyEqual(string $fieldName, array $data, string &$error, array $params): bool
    {
        if ($data[$fieldName] !== $params['value']){
            $error = sprintf(
                '%s must be exactly %s',
                $fieldName,
                $params['value']
            );
            return false;
        }
        return true;
    }

    /**
     * Checks if the value exists in the data array and is a numeric value
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Array containing some params to modify function behavior
     * 
     * - `cast` - if true, the function will cast the value to a float.
     * 
     * @return bool
     * @see Validator::validate
     */
    public static function isFloat(string $fieldName, array &$data, string &$error, array $params): bool
    {
        if (!is_numeric($data[$fieldName])){
            $error = sprintf(
                '%s must be a number.',
                $fieldName
            );
            return false;
        }
        if ($params['cast'] && !is_float($data[$fieldName])){
            $data[$fieldName] = (float) $data[$fieldName];
        }
        return true;
    }

    /**
     * Checks if the value in the data array is greater than or equal to the value in the params array
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Array containing the value to compare to
     * 
     * The params array should have the following keys:
     * 
     * - `value` - the value to compare to
     * 
     * @return bool
     * @see Validator::validate
     */
    public static function greaterThan(string $fieldName, array $data, string &$error, array $params): bool
    {
        if ($data[$fieldName] <= $params['value']){
            $error = sprintf(
                '%s must be greater than %s',
                $fieldName,
                $params['value']
            );
            return false;
        }
        return true;
    }

    /**
     * Checks if the value in the data array is smaller than or equal to the value in the params array
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Array containing the value to compare to
     * 
     * The params array should have the following keys:
     * 
     * - `value` - the value to compare to
     * 
     * @return bool
     * @see Validator::validate
     */
    public static function smallerThan(string $fieldName, array $data, string &$error, array $params): bool
    {
        if ($data[$fieldName] >= $params['value']){
            $error = sprintf(
                '%s must be smaller than %s',
                $fieldName,
                $params['value']
            );
            return false;
        }
        return true;
    }

    /**
     * Checks if the value in the data array is in the list of values in the params array
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Array containing the list of values to compare to
     * 
     * The params array should have the following keys:
     * 
     * - `values` - the list of values to compare to
     * 
     * @return bool
     * @see Validator::validate
     */
    public static function inList(string $fieldName, array $data, string &$error, array $params): bool
    {
        if (!in_array($data[$fieldName], $params['values'])){
            $error = sprintf(
                '%s is not a valid value. Choose one of: %s',
                $fieldName,
                implode(', ', $params['values'])
            );
            return false;
        }
        return true;
    }

    public static function isInt(string $fieldName, array &$data, string &$error, array $params): bool
    {   
        if (is_int($data[$fieldName])){
            return true;
        }
        if (is_string($data[$fieldName]) && ctype_digit($data[$fieldName])) {
            if ($params['cast']){
                $data[$fieldName] = (int) $data[$fieldName];
            }
            return true;
        }
        $error = sprintf(
            '%s must be an integer.',
            $fieldName
        );
        return false;
    }
}