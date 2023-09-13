<?php

namespace App\Validation;
use App\Models\EquipmentModel;
use CodeIgniter\Database\RawSql;

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
     * @param $value  Value given by CodeIgniter validator, considered as an equipment id for the parent id
     * @param string $params "<child_id>"
     * <child_id> The id for the child equipment to be used by the parent
     * @param array $data Ignored
     * @param ?string &$error Error string, formated automatically by the function
     * 
     * @return bool True if the data was approved on this rule
     */ 
    public function id_is_valid_parent($value, string $params, array $data, ?string &$error): bool
    {
        $params = explode(',', $params);
        $childId = $params[0];
        $typeName = $params[1];
        $parentId = $value;

        $query = 'SELECT e.id, children_type_ids, max_count FROM equipments e ';
        $query .= 'LEFT JOIN equipment_types et ON e.type_id = et.id ';
        $query .= 'LEFT JOIN types_hierarchy th ON th.parent_type_id = et.id ';
        $query .= sprintf(
            'WHERE (SELECT id FROM equipment_types WHERE name=\'%s\') MEMBER OF(children_type_ids) ',
            $typeName
        );
        $query .= sprintf(
            'AND e.id = \'%s\' ',
            $parentId
        );
        $query .= 'LIMIT 1';
        $query = new RawSql($query);
        $equipmentModel = model('EquipmentModel');
        $parent = $equipmentModel->db->query($query)->getResult()[0];
        d($parent);
        // For some reason the query doesn't work when built with the builder
        if (empty($parent)){
            $error = sprintf(
                'You cannot assign equipment %s as parent to %s. (Parent doesn\'t allow this child equipment type)',
                $parentId,
                $childId)
            ;
            return false;
        }
        $childrenTypeIds = json_decode($parent->children_type_ids);
        d($childrenTypeIds);
        $count = $equipmentModel->queryChilds($parentId, $childrenTypeIds, [$childId])->countAllResults();
        d($count);
        if ($count >= $parent->max_count){
            $error = sprintf(
                'You cannot assign equipment %s as parent to %s. Parent is already filled with max number of equipments (%s)',
                $parentId,
                $childId,
                $parent->max_count
            );
            return false;
        }
        return true;
    }
}