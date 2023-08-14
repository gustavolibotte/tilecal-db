<?php

namespace App\Library\Validation;

class DatabaseRules{

    public function is_model_unique($value, string $params, array $data, ?string &$error): bool
    {   
        return !$this->is_model_not_unique($value, $params, $data, $error);
    }

    public function is_model_not_unique($value, string $params, array $data, ?string &$error): bool
    {
        $params = explode(',', $params);
        $column = $params[0];
        $modelId = $params[1];
        $db = db_connect();
        $queryText = "SELECT {$column} FROM `equipments` WHERE `{$column}` = '{$value}' AND model_id = {$modelId} LIMIT 1";
        $query = $db->query($queryText);
        $results = $query->getFirstRow();
        return isset($results);
    }
}