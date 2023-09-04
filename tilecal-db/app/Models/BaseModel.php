<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Config\Factories;

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

    /**
     * Timestamp to be used on operation logs. Initialized as the request timestamp given by the filter request_time
     * @var \CodeIgniter\I18n\Time $opTimestamp
     */
    public \CodeIgniter\I18n\Time $opTimestamp;

    protected function initialize()
    {
        $this->resetOpTimestamp();
    }

    public function getIdArray(?array $ids=null){
        $results = [];
        $iterator = (is_null($ids))? $this->findAll(): $this->find($ids);
        foreach($iterator as $entity){
            $results[$entity->id] = $entity;
        }
        return $results;
    }

    public function getFormDropdownOptions(array $columns, string $separator = ' - '){
        helper('string');
        $entities = $this->asArray()->findAll();
        $options['null'] = 'Select an option';
        foreach ($entities as $entity){
            $first = true;
            $options[$entity['id']] = '';
            foreach ($columns as $colName){
                if (!empty($entity[$colName])){
                    if ($first){
                        $options[$entity['id']] = $entity[$colName];
                        $first = false;
                    } else{
                        $options[$entity['id']] .= $separator;
                        $options[$entity['id']] .= $entity[$colName];
                    }
                }
            }
        }
        return $options;
    }

    public function resetOpTimestamp()
    {
        $this->opTimestamp = service('time')->getRequestTimestamp();
    }
}
