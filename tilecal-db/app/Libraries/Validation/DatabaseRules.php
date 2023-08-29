<?php

namespace App\Library\Validation;
use App\Models\EquipmentModel;

class DatabaseRules{

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
        $equipment = $equipmentModel->getEquipment($value);
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

    /**
     * Checks if the parent equipment has not reached the max number of childs
     *
     * @param $value  Value given by CodeIgniter validator, considered as an equipment id
     * @param string $params <max>,<type> <max> is the maximum number of childs of type name <type> the parent equipment can have
     * @param array $data Ignored
     * @param ?string &$error Error string in the format "The given parent equipment (%s) already has the maximum of linked childs (%d)."
     * 
     * @return bool True if the data was approved on this rule 
     */ 
    public function not_maxed_parent($value, string $params, array $data, ?string &$error): bool
    {
        helper('string');
        $params = explode(',', $params);
        $maxChilds = $params[0];
        $typeName = $params[1];
        $equipmentModel = new EquipmentModel();
        $count = $equipmentModel->join('equipment_types', 'equipments.type_id = equipment_types.id')
                                    ->where('equipment_types.name', $typeName)
                                    ->where('equipments.parent_id', $value)
                                    ->countAllResults();
        $approved = ($count < $maxChilds);
        if (!$approved){
            $error = sprintf(
                "The given parent equipment (%s) already has %d %s childs. It is maximum is %d ",
                $value,
                $count,
                snake2Capitalized($typeName),
                $maxChilds
            );
        }
        return $approved;
    }
}