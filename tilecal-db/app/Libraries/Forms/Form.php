<?php

namespace App\Library\Forms;

abstract class Form{

    public $formValues;
    public $validator;
    public $fields;
    public $validationRules;
    protected $isValid = false;

    public abstract function getEntity();

    public function __construct($formValues=null)
    {
        $this->formValues = (is_null($formValues))? []: $formValues;
        $this->validator = \Config\Services::validation();
        $this->initialize();
        $this->validator->setRules($this->validationRules);
    }

    protected function initialize(){}

    public function validate(){
        $this->isValid = $this->validator->run($this->formValues);
        return $this->isValid;
    }

    public function getErrors(){
        return $this->validator->getErrors();
    }


    public function toHTML(){
        helper('app_form');
        helper('array');
        $str = '';
        if (!$this->isValid){
            foreach ($this->getErrors() as $errorMsg){
                $str .= form_alert($errorMsg, 'danger');
            }
        }
        
        
        foreach ($this->fields as $name=>$field) {
            $str .= form_field(
                dot_array_search('id', $field),
                dot_array_search('fieldType', $field),
                dot_array_search('fieldParams', $field),
                dot_array_search($name, $this->formValues),
                dot_array_search('required', $field),
                dot_array_search('helpText', $field),
                dot_array_search('label', $field));
        }
        return $str;
    }

    public function clear(){
        $this->isValid = false;
        $this->formValues = [];
    }
}
