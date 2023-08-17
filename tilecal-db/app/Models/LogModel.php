<?php

// This model re implements CRUD with trans methods so all database operations are made on the same transaction
// This is important since we are writing in more than one table
// This also ensures that each operation timestamps are equal

namespace App\Models;

use App\Entities\OperationLog;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Entity\Entity;
use Exception;

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
        $this->allowedFields[] = 'deleted_at';
    }

    public function insertWithLog($data = null, bool $returnID = true)
    {
        if (is_array($data)){
            foreach($data as $sample){
                $res[] = $this->insert($sample, $returnID);
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
        $changes = unset_array_keys($changes, $this->insertChangesIgnore);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'insert',
            'table' => $this->table,
            'created_at' => $opTimestamp
        ]);
        return $operationLog;
    }

    public function updateWithLog($id = null, $data = null): bool
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
        $old = $this->find($id)->toArray();
        $new = unset_array_keys($new, $this->updateChangesIgnore);
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

    public function deleteWithLog($id = null, bool $purge = false)
    {
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->delete($sampleId, $purge);
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
        $data = $this->find($id)->toArray();
        $changes['id'] = $id;
        $changes = unset_array_keys($data, $this->deleteChangesIgnore);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'delete',
            'table' => $this->table,
            'created_at' => $opTimestamp
        ]);
        return $operationLog;

    }

    public function restoreWithData(int|string|array $id): bool
    {
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->restore($sampleId);
            }
            return $res;
        }
        $histModel = new HistModel($this->isMigration);
        $opTimestamp = Time::now();
        $data = $this->onlyDeleted()->find($id);
        if (is_null($data)){
            throw new DataException('Called restore on a non deleted row.');
        }
        $operationLog = $this->getRestoreLog($opTimestamp, $id, $data);
        $this->db->transStart();
        $this->update($id, ['deleted_at' => null]);
        $histModel->insert($operationLog);
        $this->db->transComplete();

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
