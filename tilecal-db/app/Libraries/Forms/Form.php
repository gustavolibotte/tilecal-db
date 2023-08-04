<?php

namespace App\Library\Forms;
use \CodeIgniter\HTTP\IncomingRequest;

abstract class Form{

    public ?IncomingRequest $request;
    public $validator;
    public $fields;
    public $validationRules;
    protected bool $isValid = false;
    protected bool $calledValidate= false;

    public abstract function getEntity();

    public function __construct(?IncomingRequest $request=null)
    {
        $this->request = $request;
        $this->validator = \Config\Services::validation();
        $this->initialize();
        $this->validator->setRules($this->validationRules);
    }

    protected function initialize(){}

    public function validate(){
        if (is_null($this->request)){
            throw new \Exception('Cannot validate a form without a request associated to it');
        }
        $this->isValid = $this->validator->withRequest($this->request)->run();
        $this->calledValidate = true;
        return $this->isValid;
    }

    public function getErrors(){
        if (!$this->calledValidate){
            throw new \Exception("can only call getErrors after calling validate.");
        }
        return $this->validator->getErrors();
    }

    public function toHTML(){
        helper('app_form');
        helper('array');
        $str = '';
        if ($this->calledValidate and !$this->isValid){
            foreach ($this->getErrors() as $errorMsg){
                $str .= form_alert($errorMsg, 'danger');
            }
        }
        
        foreach ($this->fields as $name=>$field) {
            if (is_null($this->request)){
                $fieldValue = dot_array_search('value', $field);
            } else{
                $fieldValue = $this->request->getVar($name);
            }
            $str .= form_field(
                dot_array_search('id', $field),
                dot_array_search('fieldType', $field),
                dot_array_search('fieldParams', $field),
                $fieldValue,
                dot_array_search('required', $field),
                dot_array_search('helpText', $field),
                dot_array_search('label', $field));
        }
        return $str;
    }

    public function clear(){
        $this->isValid = false;
        $this->request = null;
        $this->calledValidate = false;
    }
}
