<?php

namespace App\Validation\Validators;
use ValueError;

abstract class EntryValidator
{
    public array $validationRules = [];

    private array $fieldValues = [];

    private $validator;

    private bool $isValid = false;

    public function __construct()
    {
        $this->validator = \Config\Services::validation(getShared: false);
        $this->initialize();
        // foreach ($this->validationRules as $field => $rules) {
        //     $this->fieldValues[$field] = null;
        // }
    }

    protected function initialize(){}

    protected function getData(){return $this->getFieldValues();}

    public function addRule(string $field, string $rule, ?string $error = null, string $where='end'): Self
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
        return $this;
    }

    public function removeRule(string $field, string $oldRule): ?string
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

    public function validate(): bool
    {
        $this->validator->reset();
        $this->validator->setRules($this->validationRules);
        $this->isValid = $this->validator->run($this->getFieldValues());
        return $this->isValid;
    }

    public function getErrors(): array
    {
        return $this->validator->getErrors();
    }

    public function getValidatedData()
    {
        return ($this->isValid)? $this->getData(): false;
    }

    protected function fieldValueIsEmpty(mixed $value)
    {
        return empty($value) || ($value == FORM_SELECT_NULL_OPTION);
    }

    public function getFieldValues(): array
    {   
        return $this->fieldValues;
    }

    public function setFieldValues(array $data): Self
    {
        $this->isValid = false;
        foreach ($data as $key=>$value){
            if (array_key_exists($key, $this->validationRules)){
                $this->fieldValues[$key] = ($this->fieldValueIsEmpty($value))? null: $value;
            }
        }
        return $this;
    }

    public function addRequest($request): Self
    {
        foreach ($this->validationRules as $fieldName=>$fieldParams){
            $requestValue = $request->getVar($fieldName);
            if (is_null($reuqestValue)){
                $requestValue = $request->getFile($fieldName);
            }
            $values2Set[$fieldName] = $requestValue;
        }
        $this->setFieldValues($values2Set);
        return $this;
    }

    public function clearState(): Self
    {
        $this->fieldValues = [];
        return $this;
    }
}
