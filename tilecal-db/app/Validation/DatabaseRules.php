<?php

namespace App\Validation;

class DatabaseRules
{
    public function is_valid_column($value, string $params, array $data, ?string &$error): bool
    {   
        $tableName = $params;
        $db = db_connect();
        $query = sprintf(
            "SHOW COLUMNS FROM %s LIKE '%s'",
            $tableName,
            $value
        );
        $res = $db->query($query)->getFirstRow();
        return isset($res);
    }
}
