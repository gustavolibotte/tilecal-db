<?php

namespace App\Models;

use CodeIgniter\Entity\Entity;
use CodeIgniter\Model;
use App\Validation\Validators\EntryValidator;
use App\Library\Readers\CSVFileReader;
use App\Library\Loggers\BlankLogger;
use Psr\Log\LoggerInterface;

abstract class BaseModel extends Model
{

    protected $DBGroup          = 'default';
    protected $table;
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = true;
    protected $cleanValidationRules = false;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function initialize(){}

    public function getIdArray(?array $ids=null){
        $results = [];
        $iterator = (is_null($ids))? $this->findAll(): $this->find($ids);
        foreach($iterator as $entity){
            $results[$entity->id] = $entity;
        }
        return $results;
    }

    public function getFormDropdownOptions(array $columns, string $separator = ' - ', string $value='id'){
        helper(['string', 'app_form']);
        $entities = $this->asArray()->findAll();
        $options = get_blank_dropdown();
        foreach ($entities as $entity){
            $first = true;
            $options[$entity[$value]] = '';
            foreach ($columns as $colName){
                if (!empty($entity[$colName])){
                    if ($first){
                        $options[$entity[$value]] = $entity[$colName];
                        $first = false;
                    } else{
                        $options[$entity[$value]] .= $separator;
                        $options[$entity[$value]] .= $entity[$colName];
                    }
                }
            }
        }
        return $options;
    }

    public function getCustomArray(string $keyField, ?string $valueField=null, ?array $ids=null, ): array
    {
        $nullValue = is_null($valueField);
        if(!$nullValue){
            if ($keyField == $valueField){
                $this->select(sprintf(
                    '`%s`',
                    $keyField
                ));
            } else{
                $this->select(sprintf(
                    '`%s`, `%s`',
                    $keyField,
                    $valueField
                ));
            }
        }
        $results = (is_null($ids))? $this->findAll(): $this->find($ids);
        $array = [];
        if ($nullValue) {
            foreach ($results as $result){
                $array[$result->$keyField] = $result;
            }
        } else {
            foreach ($results as $result){
                $array[$result->$keyField] = $result->$valueField;
            }
        }
        return $array;
    }

    private function fileAction(
        string $filepath,
        EntryValidator $validator,
        ?LoggerInterface $logger=null,
        string $action): bool|array|string
    {
        helper(['filesystem', 'app_array']);
        $fieldRename = $validator->fieldRename;
        $logger = is_null($logger)? new BlankLogger(): $logger;
        $inputFile = new CSVFileReader($filepath, headerRename:$fieldRename);
        $loggerContext = ['iRow' => null, 'action' => ucfirst($action)];
        $this->db->transBegin();
        foreach ($inputFile as $iRow=>$row){
            $loggerContext['iRow'] = $iRow;
            // Removes some fields that may conflict with the type defined in the validator
            remove_value('type_name', $row, false);
            remove_value('type_id', $row, false);
            $validator->clearState()->setFieldValues($row);
            if ($validator->validate()){
                $validatedRow = $validator->getValidatedData();
                $this->saveRow($validatedRow, $action);
                $logger->info('{iRow} {action} with success', $loggerContext);
            } else{
                $errors =  $validator->getErrors();
                $errors['iRow'] = $iRow;
                $this->db->transRollback();
                return $errors;
            }
        }
        $this->db->transCommit();
        return true;
    }

    protected function saveRow($validatedRow, string $action)
    {
        if ($action === 'update'){
            $this->update($validatedRow->id, $validatedRow);
        } else{
            $this->insert($validatedRow);
        }
        return true;
    }

    public function insertFromFile(
        string $filepath,
        EntryValidator $validator,
        ?LoggerInterface $logger=null
    )
    {
        return $this->fileAction($filepath, $validator, $logger, 'insert');
    }

    public function updateFromFile(
        string $filepath,
        EntryValidator $validator,
        ?LoggerInterface $logger=null
    )
    {
        return $this->fileAction($filepath, $validator, $logger, 'update');
    }
}
