<?php

namespace App\Models;

use App\Entities\OperationLog;
use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;
use CodeIgniter\Database\Exceptions\DataException;


class FileModel extends LogModel
{
    protected $table            = 'files';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\File::class;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'mime', 'filedata', 'description'];

    public function initialize(){
        parent::initialize();
        helper(['app_array']);
        $this->insertChangesIgnore[] = 'filedata';
        $this->updateChangesIgnore[] = 'filedata';
    }

    /**
     * Returns an array with all the related files to a specific equipment given its id
     *
     * @param string|int $id equipment id to get related files from
     * 
     * @param array array with related files entities
     */
    public function getEquipmentRelated(string|int $id): array
    {
        $query = $this->select('files.id, files.name, files.description, files.created_at, files.deleted_at')
                       ->join('equipments_to_files', 'files.id = equipments_to_files.file_id')
                       ->where('equipments_to_files.equipment_id', $id);
        if ($this->tempUseSoftDeletes){
            $query = $query->where('equipments_to_files.deleted_at IS NULL');
        }
        $result = $query->findAll();
        return $result;
    }

    public function getFile($id=null)
    {
        $query = $this->select('id, name, mime, description, created_at, updated_at, deleted_at');
        if (is_null($id)){
            $result = $query->findAll();
        }
        else{
            $result = $query->find($id);
        }

        return $result;
    }

    protected function makeLog(array $data, string $parentMethod): OperationLog
    {
        $operationLog = parent::$parentMethod($data);
        if (array_key_exists('filedata', $operationLog->changes)){
            $operationLog->filedata = remove_value('filedata', $operationLog->changes['filedata']);
        }
        return $operationLog;
    }

    protected function getInsertLog(array $data): OperationLog
    {
        $filedata = remove_value('filedata', $data['data']);
        $operationLog = parent::getInsertLog($data);
        $operationLog->filedata = $filedata;
        return $operationLog;
    }

    protected function getUpdateLog(array $data): OperationLog
    {
        $old = $this->find($data['id']);
        $filedata = remove_value('filedata', $data['data']);
        $operationLog = parent::getUpdateLog($data);
        if ($old->filedata != $filedata){
            $operationLog->filedata = $filedata;
        }
        return $operationLog;
    }

    protected function getDeleteLog(array $data): OperationLog
    {
        $old['data'] = $this->find($data['id'][0])->toRawArray();
        $old['id'] = $data['id'][0];
        $operationLog = $this->getInsertLog($old);
        $operationLog->action = 'delete';
        return $operationLog;
    }

    public function deleteRelations(int|string|array $id): bool
    {
        $equipments2FilesModel = model('Equipments2FilesModel');
        $whereClause = (is_array($id))? 'whereIn': 'where';
        $relations = $equipments2FilesModel
            ->select('id')
            ->$whereClause('file_id', $id)
            ->findAll();
        foreach ($relations as $relation){
            $equipments2FilesModel->delete($relation->id, true);
        }
        return true;
    }

    protected function deleteRelationsCallback(array $data): array
    {
        $this->deleteRelations($data['id']);
        return $data;
    }
}
