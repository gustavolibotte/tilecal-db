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
    protected $cleanValidationRules = false;

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

    /**
     * Substitutes a foreign key field based on some alias fields
     * 
     * For example, if type_id is a foreign key field, and type_name is an alias field,
     * this method will include the type_id based on the type_name.
     * 
     * @param string $fkField The foreign key field
     * @param array $aliasesFields The alias fields
     * @param array $data The data to be processed
     * @param BaseModel $model The model to be used to search for values
     * 
     * @return array The data with the foreign key field included and the aliases removed
     */
    public function substituteFK(
        string $fkField,
        array $aliasesFields,
        array $data,
        BaseModel $model
    ): array {
        helper('app_array');
        $found = array_key_exists($fkField, $data);
        foreach ($aliasesFields as $aliasField) {
            if ($found && array_key_exists($aliasField, $data)) {
                unset($data[$aliasField]);
                continue;
            } elseif ($found && !array_key_exists($aliasField, $data)) {
                continue;
            } elseif (!$found && !array_key_exists($aliasField, $data)) {
                continue;
            } elseif (!$found && array_key_exists($aliasField, $data)) {
                $fkValue = $model->select('id')
                    ->where($aliasField, $data[$aliasField])
                    ->first()->id;
                $data[$fkField] = $fkValue;
                unset($data[$aliasField]);
                $found = true;
                continue;
            }
        }
        return $data;
    }
}
