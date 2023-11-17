<?php

namespace App\Library\Forms;
use \CodeIgniter\HTTP\IncomingRequest;
use ValueError;

abstract class Form{

    protected ?IncomingRequest $request;
    public array $fields = [];
    private array $fieldValues;

    public function __construct(?IncomingRequest $request=null)
    {
        $this->request = $request;
        $this->validator = \Config\Services::validation(getShared: false);
        $this->initialize();
        if (!is_null($request)){
            $this->processRequest($request);
        }
    }

    protected function initialize(){
        $this->fieldValues = [];
        foreach ($this->fields as $name=>$params){
            $this->fieldValues[$name] = null;
        }
    }

    protected function processRequest($request): Self
    {
        $values2Set = [];
        foreach ($this->fields as $fieldName=>$fieldParams){
            if ($fieldParams['fieldType'] === 'upload'){
                $requestFieldValue = $request->getFile($fieldName);
            } else{
                $requestFieldValue = $request->getVar($fieldName);
            }
            if ($this->fieldValueIsEmpty($requestFieldValue)){
                $values2Set[$fieldName] = null;
            } else{
                $values2Set[$fieldName] = $requestFieldValue;
            }
        }
        $this->setFieldValues($values2Set);
        return $this;
    }

    protected function fieldValueIsEmpty(mixed $value){
        return $value == FORM_SELECT_NULL_OPTION;
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
        $this->initialize();
    }

    public function removeField(string $fieldName){
        unset($this->fields[$fieldName]);
        unset($this->validationRules[$fieldName]);
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
        return array_key_exists($fieldName, $this->fields);
    }
}
