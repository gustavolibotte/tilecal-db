<?php

namespace App\Validation\Validators;
use App\Library\Loggers\BlankLogger;
use App\Library\Writers\CSVFileWriter;
use App\Library\Readers\CSVFileReader;
use Throwable;
use Psr\Log\LoggerInterface;

abstract class EntryValidator
{
    public array $validationRules = [];

    public array $updateRules = [];

    private array $fieldValues = [];

    public array $fileFields = [];
    private $validator;

    private bool $isValid = false;

    protected bool $isEdit = false;

    public array $fieldRename = [];

    public function __construct()
    {
        $this->validator = \Config\Services::validation(getShared: false);
        $this->initialize();
        if (empty($this->updateRules)){
            $this->updateRules = $this->validationRules;
        }
    }

    public function asEdit()
    {   
        $this->isEdit = true;
        return $this;
    }

    public function unsetEdit()
    {
        $this->isEdit = false;
        return $this;
    }

    protected function initialize(){}

    protected function getData(){return $this->getFieldValues();}

    public function validate(): bool
    {
        $this->validator->reset();
        if ($this->isEdit){
            $rules2Set = ['id' => $this->updateRules['id']];
            foreach($this->getFieldValues() as $field=>$value){
                if (array_key_exists($field, $this->updateRules) && $field !== 'id'){
                    $rules2Set[$field] = $this->updateRules[$field];
                }
            }
            $this->validator->setRules($rules2Set);
        } else{
            $this->validator->setRules($this->validationRules);
        }
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
        return $value === '' || ($value === FORM_SELECT_NULL_OPTION) || $value === [] || is_null($value);
    }

    public function getFieldValues(): array
    {   
        return $this->fieldValues;
    }

    public function setFieldValues(array $data): Self
    {
        $this->isValid = false;
        foreach ($data as $key=>$value){
            if (array_key_exists($key, $this->getActiveRules())){
                $this->fieldValues[$key] = ($this->fieldValueIsEmpty($value))? null: $value;
            }
        }
        return $this;
    }

    public function addRequest($request): Self
    {
        $values2Set = [];
        foreach ($this->getActiveRules() as $fieldName=>$fieldParams){
            $requestValue = $this->getValueFromRequest($request, $fieldName);
            if ($this->fieldValueIsEmpty(($requestValue))){
                continue;
            }
            $values2Set[$fieldName] = $requestValue;
        }
        $this->setFieldValues($values2Set);
        return $this;
    }

    protected function getValueFromRequest($request, $fieldName){
        if (in_array($fieldName, $this->fileFields)){
            $uploadedFile = $request->getFile($fieldName);
            if (is_null($uploadedFile)){
                return null;
            }
            if (!is_uploaded_file($uploadedFile->getRealPath())){
                return null;
            }
            return $uploadedFile;
        } else{
            return $request->getVar($fieldName);
        }
    }

    public function withRequest($request): Self
    {
        $this->validator->withRequest($request);
        return $this;
    }

    public function clearState(): Self
    {
        $this->fieldValues = [];
        return $this;
    }

    protected function substituteFK(
        string $fkField,
        string $aliasField,
        string $aliasCol,
        string $modelName,
        array $data,
        bool $nullableFK=false): array
    {
        helper('app_array');
        $fkValue = dot_search_with_default($fkField, $data);
        $emptyFK = empty($fkValue);
        $aliasValue = dot_search_with_default($aliasField, $data);
        $emptyAlias = empty($aliasValue);
        if ($emptyFK && $emptyAlias){    // When both possibe FK identifiers are empty
            return $data;
        } elseif (!$emptyFK && $emptyAlias){    // When alias is empty and FK id is not
            $aliasValue = model($modelName)
                ->select($aliasCol)
                ->find($fkValue)
                ->$aliasCol;
            $data[$aliasField] = $aliasValue;
            return $data;
        } elseif ($emptyFK && !$emptyAlias){    // When fk Id is empty and alias is not
            $id = model($modelName)
            ->select('id')
            ->where($aliasCol, $aliasValue)
            ->first()->id;
            $data[$fkField] = $id;
            return $data;
        } else{    //When both of them are not empty, sets the alias with the same value as the id
            $aliasValue = model($modelName)
                ->select($aliasCol)
                ->find($fkValue)
                ->$aliasCol;
            $data[$aliasField] = $aliasValue;
            return $data;
        }
    }

    public function getActiveRules()
    {
        return ($this->isEdit)? $this->updateRules: $this->validationRules;
    }
}
