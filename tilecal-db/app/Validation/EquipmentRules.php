<?php

namespace App\Validation;
use App\Models\EquipmentModel;

class EquipmentRules{


    public static int $serialMaxSize = 36;
    public static int $maxCommentsSize = 65535; //MySQL max text size

    /**
     * Checks if the a value doesn't exist for a given type. A negation of is_type_not_unique
     *
     * @param $value  Value given by CodeIgniter validator
     * @param string $params Params with the format <column>,<typeid>. column is the equipments column to check for the given type id
     * @param array $data Ignored
     * @param ?string &$error Ignored
     * 
     * @return bool True if the data is unique on that column for a given equipment type
     */ 
    public function is_type_unique($value, string $params, array $data, ?string &$error): bool
    {   
        return !$this->is_type_not_unique($value, $params, $data, $error);
    }

    /**
     * Checks if the a value already exists for a given type
     *
     * @param $value  Value given by CodeIgniter validator
     * @param string $params Params with the format <column>,<typeid>. column is the equipments column to check for the given type id
     * @param array $data Ignored
     * @param ?string &$error Ignored
     * 
     * @return bool True if the data is not unique on that column for a given equipment type
     */ 
    public function is_type_not_unique($value, string $params, array $data, ?string &$error): bool
    {
        $params = explode(',', $params);
        $column = $params[0];
        $typeId = $params[1];
        $db = db_connect();
        $queryText = sprintf("SELECT {$column} FROM `equipments` WHERE `{$column}` = '{$value}' AND type_id = %d LIMIT 1", $typeId);
        $query = $db->query($queryText);
        $results = $query->getFirstRow();
        return isset($results);
    }

    /**
     * Checks if the equipment id is of a given type
     *
     * @param $value  Value given by CodeIgniter validator, considered as an equipment id
     * @param string $params Equipment type name
     * @param array $data Ignored
     * @param ?string &$error Error string in the format "The parent id must be from a %s. The given id is from a %s"
     * 
     * @return bool True if the data was approved on this rule 
     */ 
    public function equipment_is_type($value, string $params, array $data, ?string &$error): bool
    {   
        helper('string');
        $equipmentModel = new EquipmentModel();
        $equipment = $equipmentModel->fullQuery()->find($value);
        $approved = $equipment->type_name == $params;
        if (!$approved){
            $error = sprintf(
                "The parent id must be from a %s. The given id is from a %s",
                snake2Capitalized($params),
                $equipment->type_label
            );
        }
        return $approved;
    }
}