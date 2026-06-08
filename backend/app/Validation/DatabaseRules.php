<?php

namespace App\Validation;

class DatabaseRules	{

    /**
     * Check if a value does not exist in a database table
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Parameters for the check
     * The structure of the $params array should be:
     * ```php
     * [
     * 'table' => 'table_name',
     * 'column' => 'column_name',
     * 'ignore_id' => true // Optional: Ignores the record with the 'id' present in $data (useful for updates)
     * ]
     * ```
     * @return bool
     * @see Validator::validate
     */
    public static function isUnique(
        string $fieldName,
        array $data,
        string &$error='',
        array $params)
    {
        $table = $params['table'];
        $column = $params['column'];
        $value = $data[$fieldName];
        $db = db_connect();
        
        $builder = $db->table($table)
            ->select('1')
            ->where($column, $value);

        // Allow ignoring a specific row based on ID (for updates)
        if (isset($params['ignore_id']) && $params['ignore_id'] === true && isset($data['id'])) {
            $builder->where('id !=', $data['id']);
        }
        
        $builder->get(1)->getResultArray();

        if ($db->affectedRows() !== 0){
            $error = sprintf(
                '%s %s already exists in the database',
                $fieldName, $value
            );
            return false;
        };
        return true;
    }

    /**
     * Check if a value exist in a database table
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Parameters for the check
     * The structure of the $params array should be:
     * ```php
     * [
     * 'table' => 'table_name',
     * 'column' => 'column_name'
     * ]
     * ```
     * @return bool
     * @see Validator::validate
     */
    public static function isNotUnique(
        string $fieldName,
        array $data,
        string &$error='',
        array $params)
    {
        $table = $params['table'];
        $column = $params['column'];
        $value = $data[$fieldName];
        $db = db_connect();
        $db->table($table)
            ->select('1')
            ->where($column, $value)
            ->get(1)->getResultArray();
        if ($db->affectedRows() === 0){
            $error = sprintf(
                '%s %s doesn\'t exist in the database',
                $fieldName, $value
            );
            return false;
        };
        return true;
    }

    /**
     * Check if a value is already deleted in a database table
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Parameters for the check
     * The structure of the $params array should be:
     * ```php
     * [
     * 'table' => 'table_name',
     * 'column' => 'column_name'
     * ]
     * ```
     * @return bool
     * @see Validator::validate
     */
    public static function isNotDeleted(
        string $fieldName,
        array $data,
        string &$error='',
        array $params)
    {
        $table = $params['table'];
        $column = $params['column'];
        $value = $data[$fieldName];
        $db = db_connect();
        $db->table($table)
            ->select('1')
            ->where($column, $value)
            ->where('deleted_at IS NULL')
            ->get(1)->getResultArray();
        if ($db->affectedRows() === 0){
            $error = sprintf(
                '%s %s is already deleted or doesn\'t exist',
                $fieldName, $value
            );
            return false;
        };
        return true;
    }

    /**
     * Check if a value is deleted in a database table
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Parameters for the check
     * The structure of the $params array should be:
     * ```php
     * [
     * 'table' => 'table_name',
     * 'column' => 'column_name'
     * ]
     * ```
     * @return bool
     * @see Validator::validate
     */
    public static function isDeleted(
        string $fieldName,
        array $data,
        string &$error='',
        array $params)
    {
        $table = $params['table'];
        $column = $params['column'];
        $value = $data[$fieldName];
        $db = db_connect();
        $db->table($table)
            ->select('1')
            ->where($column, $value)
            ->where('deleted_at IS NOT NULL')
            ->get(1)->getResultArray();
        if ($db->affectedRows() === 0){
            $error = sprintf(
                '%s %s is not deleted or doesn\'t exist',
                $fieldName, $value
            );
            return false;
        };
        return true;
    }
}