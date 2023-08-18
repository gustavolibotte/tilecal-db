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

    public function getEquipmentRelated($id){
        $query = $this->select('files.id, files.name, files.description, files.created_at, files.deleted_at')
                      ->join('equipments_to_files', 'files.id = equipments_to_files.file_id')
                      ->where(sprintf("equipments_to_files.equipment_id = '%s'", $id));
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

    protected function getInsertLog(Time $opTimestamp, int|string $id, Entity $data): OperationLog|array
    {
        $operationLog = parent::getInsertLog($opTimestamp, $id, $data);
        if (array_key_exists('filedata', $operationLog->changes)){
            $operationLog->filedata = remove_value('filedata', $operationLog->changes['filedata']);
        }
        return $operationLog;
    }

    protected function getUpdateLog(Time $opTimestamp, int|string $id, Entity $new, Entity $old): OperationLog|array
    {
        $operationLog = parent::getUpdateLog($opTimestamp, $id, $new, $old);
        if ($new->filedata != $old->filedata){
            $operationLog->filedata = $new->filedata;
        }
        return $operationLog;
    }

    public function deleteWithLog($id=null, bool $purge = false, ?Time $opTimestamp = null): bool
    {
        if (is_null($opTimestamp)){
            $opTimestamp = Time::now();
        }
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->deleteWithLog($sampleId, $purge, $opTimestamp);
            }
            return $res;
        }
        $histModel = new HistModel($this->isMigration);
        $data = $this->find($id);
        $data->deleted_at = $opTimestamp;
        $operationLog = $this->getDeleteLog($opTimestamp, $id);
        $equipments2FilesModel = new Equipment2FileModel();
        $relations = $equipments2FilesModel->select('id')->where('file_id', $id)->findAll();
        $this->db->transStart();
        $res = $this->delete($id, $purge);
        foreach($relations as $relation){
            $relationLog = $equipments2FilesModel->getDeleteLog($opTimestamp, $relation->id);
            $equipments2FilesModel->delete($relation->id);
            $histModel->insert($relationLog);
        }
        $histModel->insert($operationLog);
        $this->db->transComplete();
        return $res;
    }

    protected function getDeleteLog(Time $opTimestamp, int|string $id): OperationLog|array
    {
        $changes = $this->find($id)->toArray();
        $filedata = remove_value('filedata', $changes);
        unset_array_keys($changes, $this->deleteChangesIgnore);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'delete',
            'table' => $this->table,
            'created_at' => $opTimestamp,
            'filedata' => $filedata
        ]);
        return $operationLog;
    }

    public function restoreWithLog(int|string|array $id, ?Time $opTimestamp = null): bool
    {
        if (is_null($opTimestamp)){
            $opTimestamp = Time::now();
        }
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->restoreWithLog($sampleId, $opTimestamp);
            }
            return $res;
        }
        $histModel = new HistModel($this->isMigration);
        $data = $this->onlyDeleted()->find($id);
        if (is_null($data)){
            throw new DataException('Called restore on a non deleted row.');
        }
        $operationLog = $this->getRestoreLog($opTimestamp, $id, $data);
        $equipments2FilesModel = new Equipment2FileModel();
        $relations = $equipments2FilesModel->withDeleted()->select('equipments_to_files.id')
                                           ->join('equipments', 'equipments_to_files.equipment_id = equipments.id')
                                           ->where('equipments.deleted_at IS NULL')
                                           ->where('file_id', $id)
                                           ->where('equipments_to_files.deleted_at', $data->deleted_at)
                                           ->findAll();
        $this->db->transStart();
        $this->restore($id, $opTimestamp);
        foreach($relations as $relation){
            $equipments2FilesModel->restoreWithLog($relation->id, $opTimestamp);
        }
        $histModel->insert($operationLog);
        $this->db->transComplete();

        return true;
    }

    protected function getRestoreLog(Time $opTimestamp, int|string $id, Entity $data): OperationLog|array
    {
        $changes = $data->toArray();
        $filedata = remove_value('filedata', $changes);
        unset_array_keys($changes, $this->deleteChangesIgnore);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'restore',
            'table' => $this->table,
            'created_at' => $opTimestamp,
            'filedata' => $filedata
        ]);
        return $operationLog;
    }
}
