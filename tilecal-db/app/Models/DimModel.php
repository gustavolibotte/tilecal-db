<?php

namespace App\Models;

abstract class DimModel extends LogModel
{
    public function getFormDropdownOptions(){
        $entities = $this->findAll();
        $options['null'] = 'Select an option';
        foreach ($entities as $entity){
            $options[$entity->name] = sprintf('%s - %s', $entity->name, $entity->description);
        }
        return $options;
    }
}
