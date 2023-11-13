<?php

namespace App\Validation\Validators;
use ValueError;
use App\Library\Writers\CSVFileWriter;
use App\Library\Readers\CSVFileReader;
use Throwable;

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
    }

    abstract public function asEdit();

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
        return empty($value) || ($value === FORM_SELECT_NULL_OPTION);
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
            if ($this->fieldValueIsEmpty($requestValue)){
                $requestValue = $request->getFile($fieldName);
                if ($this->fieldValueIsEmpty($requestValue)){
                    continue;
                }
            }
            $values2Set[$fieldName] = $requestValue;
        }
        $this->setFieldValues($values2Set);
        return $this;
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

    public function validateFileData(string $filepath, array $fieldRename=[], string $modelName): ?array
    {
        helper(['filesystem', 'app_array']);
        $foundError = false;
        $fieldRenameFlipped = array_flip($fieldRename);
        $dataModel = model($modelName);
        $db = db_connect();
        $inputFile = new CSVFileReader($filepath, headerRename:$fieldRename);
        $logFilepath = tempnam(config('Paths')->validationLogsDirectory, '');
        $fileWriter = (new CSVFileWriter($logFilepath))->open();
        $fileWriter->addLine([
            'row', 'field', 'error'
        ]); // Writing the header in the output file
        $db->transException(true)->transBegin();
        foreach ($inputFile as $iRow=>$row){
            // Removes some fields that may conflict with the type defined in the validator
            remove_value('type_name', $row, false);
            remove_value('type_id', $row, false);
            $this->clearState()->setFieldValues($row);
            if ($this->validate()){
                try{
                    $rowData = $this->getValidatedData();
                    $dataModel->insert($rowData);
                } catch (Throwable $e){
                    $foundError = true;
                    $fileWriter->addLine([
                        $iRow,
                        'InternalError',
                        $e->getMessage()
                    ]);
                    break;
                }
            } else{
                $foundError = true;
                foreach($this->getErrors() as $field=>$errorMsg){
                    // Maps the fields back to the front end name
                    $fieldName = dot_search_with_default($field, $fieldRenameFlipped, $field);
                    $fileWriter->addLine([
                        $iRow,
                        $fieldName,
                        $errorMsg
                    ]);
                }
            }
        }
        $fileWriter->close();
        if ($foundError || ($db->transStatus() === false)){
            $db->transRollback();
            return ['status' => false, 'logFilepath' => $logFilepath];
        } else{
            $db->transCommit();
            delete_files($logFilepath);
            return  ['status' => true, 'logFilepath' => null];
        }
    }
}
