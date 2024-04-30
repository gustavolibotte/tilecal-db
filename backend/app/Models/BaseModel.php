<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Validation\Validators\EntryValidator;
use App\Library\Readers\CSVFileReader;
use App\Library\Loggers\BlankLogger;
use Psr\Log\LoggerInterface;

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
}
