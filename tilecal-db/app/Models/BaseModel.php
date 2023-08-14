<?php

namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    // Custom
    protected bool $isMigration = false;

    public function __construct(bool $isMigration=false)
    {   
        parent::__construct();
        $this->isMigration = $isMigration;
    }

    public function getIdArray(array $ids){
        $results = [];
        foreach($this->find($ids) as $entity){
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
}
