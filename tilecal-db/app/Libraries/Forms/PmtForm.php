<?php

namespace App\Library\Forms;

class PmtForm extends EquipmentForm{

    public $validationRules;
    public $fields;

    public function initialize()
    {   
        parent::initialize();
        $this->formValues['model_label'] = 'PMT';
    }

    public function clear(){
        parent::clear();
        $this->formValues['model_label'] = 'PMT';
    }
}
