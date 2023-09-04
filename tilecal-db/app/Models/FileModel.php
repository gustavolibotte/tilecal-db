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

    protected function getInsertLog(int|string $id, Entity $data): OperationLog|array
    {
        $operationLog = parent::getInsertLog($id, $data);
        if (array_key_exists('filedata', $operationLog->changes)){
            $operationLog->filedata = remove_value('filedata', $operationLog->changes['filedata']);
        }
        return $operationLog;
    }

    protected function getUpdateLog(int|string $id, Entity $new, Entity $old): OperationLog|array
    {
        $operationLog = parent::getUpdateLog($id, $new, $old);
        if ($new->filedata != $old->filedata){
            $operationLog->filedata = $new->filedata;
        }
        return $operationLog;
    }

    public function deleteWithLog($id=null, bool $purge = false): bool
    {
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->deleteWithLog($sampleId, $purge);
            }
            return $res;
        }
        $histModel = new HistModel();
        $data = $this->find($id);
        $data->deleted_at = service('time')->getRequestTimestamp();
        $operationLog = $this->getDeleteLog($id);
        $equipments2FilesModel = new Equipment2FileModel();
        $relations = $equipments2FilesModel->select('id')->where('file_id', $id)->findAll();
        $res = $this->delete($id, $purge);
        foreach($relations as $relation){
            $equipments2FilesModel->deleteWithLog($relation->id, $purge, false);
        }
        $histModel->insert($operationLog);
        return $res;
    }

    protected function getDeleteLog(int|string $id): OperationLog|array
    {
        $changes = $this->find($id)->toArray();
        $filedata = remove_value('filedata', $changes);
        unset_array_keys($changes, $this->deleteChangesIgnore);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'delete',
            'table' => $this->table,
            'filedata' => $filedata
        ]);
        return $operationLog;
    }

    public function restoreWithLog(int|string|array $id): bool
    {
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->restoreWithLog($sampleId);
            }
            return $res;
        }
        $histModel = new HistModel();
        $data = $this->onlyDeleted()->find($id);
        if (is_null($data)){
            throw new DataException('Called restore on a non deleted row.');
        }
        $operationLog = $this->getRestoreLog($id, $data);
        $equipments2FilesModel = new Equipment2FileModel();
        $relations = $equipments2FilesModel->withDeleted()->select('equipments_to_files.id')
                                           ->join('equipments', 'equipments_to_files.equipment_id = equipments.id')
                                           ->where('equipments.deleted_at IS NULL')
                                           ->where('file_id', $id)
                                           ->where('equipments_to_files.deleted_at', $data->deleted_at)
                                           ->findAll();
        $this->restore($id);
        foreach($relations as $relation){
            $equipments2FilesModel->restoreWithLog($relation->id, false);
        }
        $histModel->insert($operationLog);

        return true;
    }

    protected function getRestoreLog(int|string $id, Entity $data): OperationLog|array
    {
        $changes = $data->toArray();
        $filedata = remove_value('filedata', $changes);
        unset_array_keys($changes, $this->deleteChangesIgnore);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'restore',
            'table' => $this->table,
            'filedata' => $filedata
        ]);
        return $operationLog;
    }
}
