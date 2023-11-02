<?php

namespace App\Models;

use CodeIgniter\Model;

abstract class BaseModel extends Model
{

    protected $DBGroup          = 'default';
    protected $table;
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = true;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function initialize(){}

    public function getIdArray(?array $ids=null){
        $results = [];
        $iterator = (is_null($ids))? $this->findAll(): $this->find($ids);
        foreach($iterator as $entity){
            $results[$entity->id] = $entity;
        }
        return $results;
    }

    public function getFormDropdownOptions(array $columns, string $separator = ' - ', string $value='id'){
        helper('string');
        $entities = $this->asArray()->findAll();
        $options[FORM_SELECT_NULL_OPTION] = 'Select an option';
        foreach ($entities as $entity){
            $first = true;
            $options[$entity[$value]] = '';
            foreach ($columns as $colName){
                if (!empty($entity[$colName])){
                    if ($first){
                        $options[$entity[$value]] = $entity[$colName];
                        $first = false;
                    } else{
                        $options[$entity[$value]] .= $separator;
                        $options[$entity[$value]] .= $entity[$colName];
                    }
                }
            }
        }
        return $options;
    }

    public function getCustomArray(string $keyField, ?string $valueField=null, ?array $ids=null, ): array
    {
        $nullValue = is_null($valueField);
        if(!$nullValue){
            if ($keyField == $valueField){
                $this->select(sprintf(
                    '`%s`',
                    $keyField
                ));
            } else{
                $this->select(sprintf(
                    '`%s`, `%s`',
                    $keyField,
                    $valueField
                ));
            }
        }
        $results = (is_null($ids))? $this->findAll(): $this->find($ids);
        $array = [];
        if ($nullValue) {
            foreach ($results as $result){
                $array[$result->$keyField] = $result;
            }
        } else {
            foreach ($results as $result){
                $array[$result->$keyField] = $result->$valueField;
            }
        }
        return $array;
    }

}
