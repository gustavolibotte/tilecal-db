<?php

namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    // Custom
    protected bool $isMigration = false;
    protected $useSoftDeletes = true;
    protected $allowedFields = [];

    public function __construct(bool $isMigration=false)
    {   
        parent::__construct();
        $this->isMigration = $isMigration;
    }

    protected function initialize(){
        $this->allowedFields[] = 'deleted_at';
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

    public function restore($id): bool
    {
        return $this->withDeleted()->update($id, ['deleted_at' => null]);
    }
}
