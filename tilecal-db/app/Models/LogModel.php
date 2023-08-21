<?php

// This model re implements CRUD with trans methods so all database operations are made on the same transaction
// This is important since we are writing in more than one table
// This also ensures that each operation timestamps are equal

namespace App\Models;

use App\Entities\OperationLog;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Entity\Entity;
use App\Entities\Equipment2File;

abstract class LogModel extends BaseModel{

    protected bool $isMigration = false;
    protected $useSoftDeletes = true;
    protected $insertChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];
    protected $updateChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];
    protected $deleteChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];
    protected $restoreChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];

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

    public function insertWithLog(Entity|array $data, bool $returnID = true, ?Time $opTimestamp=null, bool $transaction=true)
    {
        if (is_null($opTimestamp)){
            $opTimestamp = Time::now();
        }
        if (is_array($data)){
            foreach($data as $sample){
                $res[] = $this->insertWithLog($sample, $returnID, $opTimestamp, $transaction);
            }
            return $res;
        }
        $histModel = new HistModel($this->isMigration);
        if (!($data instanceof Entity)){
            throw new \TypeError('$data must be instance of Entity class');
        }
        $data->created_at = $opTimestamp;
        $data->updated_at = $opTimestamp;
        if ($transaction){
            $this->db->transStart();
        }
        $res = $this->insert($data, $returnID);
        $id = $this->getInsertID();
        $operationLog = $this->getInsertLog($opTimestamp, $id, $data);
        $histModel->insert($operationLog);
        if ($transaction){
            $this->db->transComplete();
        }
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

    public function updateWithLog(int|string|array $id, $data = null, ?Time $opTimestamp=null, bool $transaction=true): bool
    {
        if (is_null($opTimestamp)){
            $opTimestamp = Time::now();
        }
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->insertWithLog($sampleId, $data, $opTimestamp,);
            }
            return $res;
        }
        $histModel = new HistModel($this->isMigration);
        if (!($data instanceof Entity)){
            throw new \TypeError('$data must be instance of Entity class');
        }
        $data->updated_at = $opTimestamp;
        $old = $this->find($id);
        $operationLog = $this->getUpdateLog($opTimestamp, $id, $data, $old);
        if ($transaction){
            $this->db->transStart();
        }
        $res = $this->update($id, $data);
        $histModel->insert($operationLog);
        if ($transaction){
            $this->db->transComplete();
        }
        return $res;
    }

    protected function getUpdateLog(Time $opTimestamp, int|string $id, Entity $new,  Entity $old):OperationLog| array
    {
        $new = $new->toArray();
        $old = $old->toArray();
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
        foreach ($new as $name=>$newValue){
            if (array_key_exists($name, $old) && ($old[$name] != $newValue)){
                $changes[$name] = $newValue;
            }
        }
        return (isset($changes))? $changes: [];
    }

    public function deleteWithLog(int|string|array $id = null, bool $purge = false, ?Time $opTimestamp=null, bool $transaction=true)
    {
        if (is_null($opTimestamp)){
            $opTimestamp = Time::now();
        }
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->deleteWithLog($sampleId, $purge, $opTimestamp, $transaction);
            }
            return $res;
        }
        $histModel = new HistModel($this->isMigration);
        $data = $this->find($id);
        $data->deleted_at = $opTimestamp;
        $operationLog = $this->getDeleteLog($opTimestamp, $id);
        if ($transaction){
            $this->db->transStart();
        }
        $res = $this->update($id, ['deleted_at' => $opTimestamp]);
        $histModel->insert($operationLog);
        if ($transaction){
            $this->db->transComplete();
        }
        return $res;
    }

    protected function getDeleteLog(Time $opTimestamp, int|string $id): OperationLog|array
    {
        $changes = $this->find($id)->toArray();
        unset_array_keys($changes, $this->deleteChangesIgnore);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'delete',
            'table' => $this->table,
            'created_at' => $opTimestamp
        ]);
        return $operationLog;

    }

    public function restoreWithLog(int|string|array $id, ?Time $opTimestamp = null, bool $transaction=true): bool
    {
        if (is_null($opTimestamp)){
            $opTimestamp = Time::now();
        }
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->restoreWithLog($sampleId, $opTimestamp, $transaction);
            }
            return $res;
        }
        $histModel = new HistModel($this->isMigration);
        $data = $this->onlyDeleted()->find($id);
        if (is_null($data)){
            throw new DataException('Called restore on a non deleted row.');
        }
        $operationLog = $this->getRestoreLog($opTimestamp, $id, $data);
        if ($transaction){
            $this->db->transStart();
        }
        $this->restore($id, $opTimestamp);
        $histModel->insert($operationLog);
        if ($transaction){
            $this->db->transComplete();
        }

        return true;
    }

    public function restore(int|string|array $id, Time $opTimestamp): bool
    {
        $this->onlyDeleted()->update($id, ['deleted_at' => null, 'updated_at' => $opTimestamp]);
        return true;
    }

    protected function getRestoreLog(Time $opTimestamp, int|string $id, Entity $data): OperationLog|array
    {
        $changes = $data->toArray();
        unset_array_keys($changes, $this->restoreChangesIgnore);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'restore',
            'table' => $this->table,
            'created_at' => $opTimestamp
        ]);
        return $operationLog;
    }
}
