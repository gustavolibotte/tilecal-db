<?php

namespace App\Models;

abstract class DimModel extends LogModel
{
    public function getFormDropdownOptions(){
        $entities = $this->findAll();
        $options['null'] = 'Select an option';
        foreach ($entities as $entity){
            if (is_null($entity->description)){
                $options[$entity->name] = $entity->name;
            } else{
                $options[$entity->name] = sprintf('%s - %s', $entity->name, $entity->description);
            }
        }
        return $options;
    }
}
