<?php

namespace App\Library\Forms;

abstract class Form{

    public $formValues;
    public $validator;
    public $fields;
    public $validationRules;
    public $isValid = false;

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
        if (!is_null($this->isValid)){
            $str .= '<div>';
            if ($this->isValid){
                $alert = form_alert('Register success', 'success');
                $str .= $alert;
            } else{
                foreach ($this->getErrors() as $errorMsg){
                    $alert = form_alert($errorMsg, 'danger');
                    $str .= $alert;
                }
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
        $this->formValues = [];
    }
}
