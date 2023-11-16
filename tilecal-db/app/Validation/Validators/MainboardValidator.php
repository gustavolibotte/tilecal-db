<?php

namespace App\Validation\Validators;

class MainboardValidator extends EquipmentValidator
{
    public function setFieldValues($data): Self
    {
        if (array_key_exists('id', $data)){
            $data['id'] = str_replace('V', '', $data['id']);
        }
        parent::setFieldValues($data);
        return $this;
    }
}
