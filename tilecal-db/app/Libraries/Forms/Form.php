<?php

namespace App\Library\Forms;
use \CodeIgniter\HTTP\IncomingRequest;
use ValueError;

abstract class Form{

    protected ?IncomingRequest $request;
    public $validator;
    public $fields = [];
    public $validationRules;
    protected bool $isValid = false;
    protected bool $calledValidate= false;
    private array $fieldValues = [];

    public abstract function getEntity();

    public function __construct(?IncomingRequest $request=null)
    {
        $this->request = $request;
        $this->validator = \Config\Services::validation(getShared: false);
        $this->initialize();
        if (!is_null($this->request)){
            $this->processRequest();
        }
        $this->validator->setRules($this->validationRules);
    }

    protected function initialize(){}

    protected function processRequest(){
        foreach (array_keys($this->fields) as $key){
            if (!array_key_exists($key, $this->fieldValues)){
                $this->fieldValues[$key] = $this->request->getVar($key);
            }
        }
    }

    public function getFieldValues(): array
    {   
        return $this->fieldValues;
    }

    public function setFieldValues(array $data)
    {
        foreach (array_keys($data) as $key){
            if (array_key_exists($key, $this->fields)){
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
        $fieldValues = $this->getFieldValues();
        foreach ($this->fields as $name=>$field) {
            $str .= form_field(
                dot_array_search('id', $field),
                dot_array_search('fieldType', $field),
                dot_array_search('fieldParams', $field),
                dot_array_search($name, $fieldValues),
                dot_array_search('required', $field),
                dot_array_search('helpText', $field),
                dot_array_search('label', $field)
            );
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

    protected function removeRule(string $field, string $oldRule): ?string
    {
        helper('app_array');
        $counter = 0;
        $found = false;
        foreach($this->validationRules[$field]['rules'] as &$rule){
            if (str_starts_with($rule, $oldRule)){
                $found = true;
                break;
            } else{
                $counter++;
                continue;
            }
        }
        if ($found){
            $removedRule = remove_value($counter, $this->validationRules[$field]['rules']);
            if (array_key_exists($oldRule, $this->validationRules[$field]['errors'])){
                unset($this->validationRules[$field]['errors'][$counter]);
            }
            return $removedRule;
        }
        return null;
    }

    protected function addRule(string $field, string $rule, ?string $error = null, string $where='end')
    {   
        if ($where == 'end'){
            $this->validationRules[$field]['rules'][] = $rule;
        } elseif ($where == 'start'){
            $this->validationRules[$field]['rules'] = array_merge(
                [$rule],
                $this->validationRules[$field]['rules']
            );
        } else{
            throw new ValueError("where param only supports 'end' and 'start' values");
        }
        if (!is_null($error)){
            $ruleName = explode('[', $rule)[0];
            $this->validationRules[$field]['errors'][$ruleName] = $error;
        }
    }

    protected function setReadOnly(string $field)
    {
        $this->fields[$field]['fieldParams']['readOnly'] = null;
    }

    protected function unsetReadOnly(string $field)
    {
        helper('app_array');
        remove_value('readOnly', $this->fields[$field]['fieldParams'], false);
    }
}
