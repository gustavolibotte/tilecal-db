<?php

namespace App\Library\Forms;
use \CodeIgniter\HTTP\IncomingRequest;

abstract class Form{

    protected ?IncomingRequest $request;
    public $validator;
    public $fields;
    public $validationRules;
    protected bool $isValid = false;
    protected bool $calledValidate= false;
    protected array $fieldValues = [];

    public abstract function getEntity();

    public function __construct(?IncomingRequest $request=null)
    {
        $this->request = $request;
        $this->validator = \Config\Services::validation();
        $this->initialize();
        if(is_null($request)){
            $this->fieldValues = array_fill_keys(array_keys($this->fields), null);
        } else{
            $this->fieldValues = $this->processRequest();
        }
        $this->validator->setRules($this->validationRules);
    }

    protected function initialize(){}

    protected function processRequest(): array
    {
        foreach (array_keys($this->fields) as $key){
            $values[$key] = $this->request->getVar($key);
        }
        return $values;
    }

    public function getFieldValues(): array
    {
        return $this->fieldValues;
    }

    public function setFieldValues(array $data)
    {
        $dataKeys = array_keys($data);
        foreach (array_keys($this->fields) as $key){
            if (in_array($key, $dataKeys)){
                $this->fieldValues[$key] = $data[$key];
            }
        }
    }

    public function validate(){
        $this->isValid = $this->validator->run($this->getFieldValues());
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
        
        foreach ($this->fields as $name=>$field) {
            $str .= form_field(
                dot_array_search('id', $field),
                dot_array_search('fieldType', $field),
                dot_array_search('fieldParams', $field),
                $this->fieldValues[$name],
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

    public function removeField(string $fieldName){
        unset($this->fields[$fieldName]);
        unset($this->validationRules[$fieldName]);
        $this->resetRules();
    }

    protected function resetRules(){
        $this->validator->reset();
        $this->validator->setRules($this->validationRules);
    }
}
