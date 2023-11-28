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

    public static function validateFile(
        string $filepath,
        EntryValidator $validator,
        string $modelName,
        array $fieldRename=[],
        ?LoggerInterface $logger=null,
        string $op='insert'): bool
    {
        helper(['filesystem', 'app_array']);
        $foundError = false;
        $fieldRenameFlipped = array_flip($fieldRename);
        $dataModel = model($modelName);
        if (is_null($logger)){
            $logger = new BlankLogger();
        }
        $db = db_connect();
        if (empty($fieldRename)){
            $fieldRename = $validator->fieldRename;
        }
        $inputFile = new CSVFileReader($filepath, headerRename:$fieldRename);
        $db->transException(true)->transBegin();
        foreach ($inputFile as $iRow=>$row){
            // Removes some fields that may conflict with the type defined in the validator
            remove_value('type_name', $row, false);
            remove_value('type_id', $row, false);
            $validator->clearState()->setFieldValues($row);
            if ($validator->validate()){
                try{
                    $rowData = $validator->getValidatedData();
                    $dataModel->insert($rowData);
                    $logger->info('{row} Inserted with success', ['row' => $iRow]);
                } catch (Throwable $e){
                    $foundError = true;
                    $message = sprintf(
                        '%s. %s: %d %s%s',
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine(),
                        PHP_EOL,
                        $e->getTraceAsString()
                    );
                    $logger->critical($message);
                    break;
                }
            } else{
                $foundError = true;
                foreach($validator->getErrors() as $field=>$errorMsg){
                    // Maps the fields back to the front end name
                    $fieldName = dot_search_with_default($field, $fieldRenameFlipped, $field);
                    $logger->error(
                        'Row {iRow} - {fieldName} - {message}',
                        [
                            'iRow' => $iRow,
                            'fieldName' => $fieldName,
                            'message' => $errorMsg
                        ]
                    );
                }
                break;
            }
        }
        if ($foundError || ($db->transStatus() === false)){
            $db->transRollback();
            return false;
        } else{
            $db->transCommit();
            return true;
        }

    }

    public function validateFileData(string $filepath, string $modelName, array $fieldRename=[]): ?array
    {
        helper(['filesystem', 'app_array']);
        $foundError = false;
        $fieldRenameFlipped = array_flip($fieldRename);
        $dataModel = model($modelName);
        $db = db_connect();
        if (empty($fieldRename)){
            $fieldRename = $this->fieldRename;
        }
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
                    $message = sprintf(
                        '%s. %s: %d',
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    );
                    $fileWriter->addLine([
                        $iRow,
                        'InternalError',
                        $message
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
