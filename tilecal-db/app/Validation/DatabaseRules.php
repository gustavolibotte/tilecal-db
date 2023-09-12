<?php

namespace App\Validation;
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
        $childId = $params;
        $parentId = $value;

        $equipmentModel = model('EquipmentModel');
        $subQuery = $equipmentModel
            ->builder()
            ->select('type_id')
            ->where('id', $childId)
            ->get();
        $parent = $equipmentModel
            ->select('equipments.id')
            ->join('equipment_types', 'equipments.type_id = equipment_types.id', 'left')
            ->join('type_hirarchies', 'equipment_types.id = type_hierarchies.parent_type_id', 'left')
            ->withDeletedJoin('equipment_types')
            ->withDeletedJoin('type_hierarchies')
            ->where(sprintf(
                '(%s) MEMBER OF(children_type_ids)',
                $subQuery
            ))
            ->find($parentId);
        
        if (empty($parent)){
            $error = sprintf(
                'You cannot assign equipment %s as parent to %s. (Parent doesn\'t allow this child equipment type)',
                $parentId,
                $childId)
            ;
            return false;
        }
        $childrenTypeIds = json_decode($parent->children_type_ids);
        $count = $equipmentModel->queryChilds($parentId, $childrenTypeIds, [$childId])->countAllResults();
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