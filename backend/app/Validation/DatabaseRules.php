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
     * [
     *     'table' => 'table_name',
     *     'column' => 'column_name'
     * ]
     * @return bool
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
        $db->table($table)
            ->select('1')
            ->where($column, $value)
            ->get(1)->getResultArray();
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
     * [
     *     'table' => 'table_name',
     *     'column' => 'column_name'
     * ]
     * @return bool
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
}