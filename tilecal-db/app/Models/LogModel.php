<?php

// This model re implements CRUD with trans methods so all database operations are made on the same transaction
// This is important since we are writing in more than one table
// This also ensures that each operation timestamps are equal

namespace App\Models;

use App\Entities\OperationLog;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Entity\Entity;

abstract class LogModel extends BaseModel{

    protected bool $isMigration = false;
    protected $useSoftDeletes = true;
    protected $insertChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];
    protected $updateChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];
    protected $deleteChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];

    public function __construct(bool $isMigration=false)
    {   
        parent::__construct();
        $this->isMigration = $isMigration;
    }

    public function initialize()
    {
        helper('app_array');
        $this->allowedFields = array_merge($this->allowedFields, ['updated_at', 'created_at', 'deleted_at']);
    }

    public function insertWithLog(Entity|array $data, bool $returnID = true)
    {
        if (is_array($data)){
            foreach($data as $sample){
                $res[] = $this->insertWithLog($sample, $returnID);
            }
            return $res;
        }
        $histModel = new HistModel($this->isMigration);
        $opTimestamp = Time::now();
        if (!($data instanceof Entity)){
            throw new \TypeError('$data must be instance of Entity class');
        }
        $data->created_at = $opTimestamp;
        $data->updated_at = $opTimestamp;
        $this->db->transStart();
        $res = $this->insert($data, $returnID);
        $id = $this->getInsertID();
        $operationLog = $this->getInsertLog($opTimestamp, $id, $data);
        $histModel->insert($operationLog);
        $this->db->transComplete();
        return $res;
    }

    protected function getInsertLog(Time $opTimestamp, int|string $id, Entity $data): OperationLog|array
    {
        $changes = $data->toArray();
        unset_array_keys($changes, $this->insertChangesIgnore);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'insert',
            'table' => $this->table,
            'created_at' => $opTimestamp
        ]);
        return $operationLog;
    }

    public function updateWithLog(int|string $id, $data = null): bool
    {
        $histModel = new HistModel($this->isMigration);
        $opTimestamp = Time::now();
        if (!($data instanceof Entity)){
            throw new \TypeError('$data must be instance of Entity class');
        }
        $data->updated_at = $opTimestamp;
        $operationLog = $this->getUpdateLog($opTimestamp, $id, $data);
        $this->db->transStart();
        $res = $this->update($id, $data);
        $histModel->insert($operationLog);
        $this->db->transComplete();
        return $res;
    }

    protected function getUpdateLog(Time $opTimestamp, int|string $id, Entity $data):OperationLog| array
    {
        $new = $data->toArray();
        unset_array_keys($old, $this->updateChangesIgnore);
        unset_array_keys($new, $this->updateChangesIgnore);
        $changes = $this->getChangedFields($old, $new);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'update',
            'table' => $this->table,
            'created_at' => $opTimestamp
        ]);
        return $operationLog;
    }

    protected function getChangedFields(array $old, array $new): array
    {
        foreach ($old as $name=>$oldValue){
            if (($new[$name] != $oldValue)){
                $changes[$name] = $new[$name];
            }
        }
        return (isset($changes))? $changes: [];
    }

    public function deleteWithLog(int|string|array $id = null, bool $purge = false)
    {
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->deleteWithLog($sampleId, $purge);
            }
            return $res;
        }
        $histModel = new HistModel($this->isMigration);
        $opTimestamp = Time::now();
        $data = $this->find($id);
        $data->deleted_at = $opTimestamp;
        $operationLog = $this->getDeleteLog($opTimestamp, $id);
        $this->db->transStart();
        $res = $this->update($id, ['deleted_at' => $opTimestamp]);
        $histModel->insert($operationLog);
        $this->db->transComplete();
        return $res;
    }

    protected function getDeleteLog(Time $opTimestamp, int|string $id): OperationLog|array
    {
        $changes = $this->withDeleted()->find($id)->toArray();
        unset_array_keys($changes, $this->deleteChangesIgnore);
        if (empty($changes)){
            d($changes);
            throw new \Exception();
        }
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'delete',
            'table' => $this->table,
            'created_at' => $opTimestamp
        ]);
        return $operationLog;

    }

    public function restoreWithLog(int|string|array $id): bool
    {
        $opTimestamp = Time::now();
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
        $this->db->transStart();
        $this->restore($id, $opTimestamp);
        $histModel->insert($operationLog);
        $this->db->transComplete();

        return true;
    }

    public function restore(int|string|array $id, Time $opTimsetamp): bool
    {
        $this->onlyDeleted()->update($id, ['deleted_at' => null, 'updated_at' => $opTimsetamp]);
        return true;
    }

    protected function getRestoreLog(Time $opTimestamp, int|string $id, Entity $data): OperationLog|array
    {
        $operationLog = new OperationLog([
            'changes' => [],
            'record_id' => $id,
            'action' =>'restore',
            'table' => $this->table,
            'created_at' => $opTimestamp
        ]);
        return $operationLog;
    }
}
