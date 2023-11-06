<?php

namespace App\Library\Forms;
use \CodeIgniter\HTTP\IncomingRequest;
use ValueError;

abstract class Form{

    protected ?IncomingRequest $request;
    public $validator;
    public array $fields = [];
    public array $validationRules =[];
    protected bool $isValid = false;
    private array $fieldValues = [];

    protected abstract function getData();

    public function getValidatedData()
    {
        if (!$this->isValid){
            throw new \Exception("Can only get Data after data is validated");
        }
        return $this->getData();
    }

    public function __construct(?IncomingRequest $request=null)
    {
        $this->request = $request;
        $this->validator = \Config\Services::validation(getShared: false);
        $this->initialize();
        foreach ($this->fields as $name=>$params){
            $this->fieldValues[$name] = null;
        }
        $this->processRequest();
        $this->validator->setRules($this->validationRules);
    }

    protected function initialize(){}

    protected function processRequest(){
        $nullRequest = is_null($this->request);
        $values2Set = [];
        foreach ($this->fields as $fieldName=>$fieldParams){
            if (!$nullRequest){
                if ($fieldParams['fieldType'] === 'upload'){
                    $requestFieldValue = $this->request->getFile($fieldName);
                } else{
                    $requestFieldValue = $this->request->getVar($fieldName);
                }
                if ($this->fieldValueIsEmpty($requestFieldValue)){
                    $values2Set[$fieldName] = null;
                } else{
                    $values2Set[$fieldName] = $requestFieldValue;
                }
            }
        }
        $this->setFieldValues($values2Set);
    }

    protected function fieldValueIsEmpty(mixed $value){
        return empty($value) || ($value == FORM_SELECT_NULL_OPTION);
    }

    public function getFieldValues(): array
    {   
        return $this->fieldValues;
    }

    public function setFieldValues(array $data)
    {
        $this->isValid = false;
        foreach (array_keys($data) as $key){
            if (array_key_exists($key, $this->fields)){
                $this->fieldValues[$key] = $data[$key];
            }
        }
    }

    public function validate(){
        $this->validator->reset();
        $this->validator->setRules($this->validationRules);
        $this->isValid = $this->validator->run($this->getFieldValues());
        return $this->isValid;
    }

    public function getErrors(){
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
    }

    public function removeField(string $fieldName){
        unset($this->fields[$fieldName]);
        unset($this->validationRules[$fieldName]);
    }

    protected function removeRule(string $field, string $oldRule): ?string
    {
        helper('app_array');
        $foundKey = null;
        foreach($this->validationRules[$field]['rules'] as $key=>$rule){
            if (str_starts_with($rule, $oldRule)){
                $foundKey = $key;
                break;
            }
        }
        if (!is_null($foundKey)){
            $removedRule = remove_value($foundKey, $this->validationRules[$field]['rules']);
            if (array_key_exists($oldRule, $this->validationRules[$field]['errors'])){
                unset($this->validationRules[$field]['errors'][$oldRule]);
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

    public function fieldExists(string $fieldName): bool
    {
        return array_key_exists($fieldName, $this->fields) && array_key_exists($fieldName, $this->validationRules);
    }
}
