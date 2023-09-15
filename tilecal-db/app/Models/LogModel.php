<?php

// This model re implements CRUD with trans methods so all database operations are made on the same transaction
// This is important since we are writing in more than one table
// This also ensures that each operation timestamps are equal

namespace App\Models;

use App\Entities\OperationLog;
use CodeIgniter\Database\Exceptions\DatabaseException;

abstract class LogModel extends BaseModel{

    protected $useSoftDeletes = true;
    protected array $insertChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];
    protected array $updateChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];
    protected array $deleteChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];
    protected array $restoreChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];
    protected array $beforeRestore = [];
    protected array $afterRestore = [];

    public function initialize()
    {
        helper('app_array');
        parent::initialize();
        $this->beforeInsert[] = 'addInsertTimestamps';
        $this->afterInsert[] = 'logInsert';
        $this->beforeUpdate[] = 'addUpdateTimestamps';
        $this->beforeUpdate[] = 'logUpdate';
        $this->beforeDelete[] = 'logDelete';
        $this->beforeDelete[] = 'addDeleteTimestamps';
        $this->beforeRestore[] = 'logRestore';
    }

    protected function addInsertTimestamps(array $data): array
    {
        $opTimestamp = service('time')->getRequestTimestamp()->format('Y-m-d H:i:s.u');
        $data['data']['created_at'] = $opTimestamp;
        $data['data']['updated_at'] = $opTimestamp;
        return $data;
    }

    protected function logInsert(array $data)
    {
        $operationLog = $this->getInsertLog($data);
        model('HistModel')->insert($operationLog);
        return $data;
    }

    protected function getInsertLog(array $data): OperationLog
    {
        unset_array_keys($data['data'], $this->insertChangesIgnore);
        $data['data']['id'] = $data['id'];
        if ($this->table == 'type_slots'){
            d($data);
        }
        $operationLog = new OperationLog([
            'changes' => $data['data'],
            'record_id' => $data['id'],
            'action' =>'insert',
            'table' => $this->table
        ]);
        return $operationLog;
    }

    protected function addUpdateTimestamps(array $data): array
    {
        $data['data']['updated_at'] = service('time')->getRequestTimestamp()->format('Y-m-d H:i:s.u');
        return $data;
    }

    protected function logUpdate(array $data)
    {
        $operationLog = $this->getUpdateLog($data);
        model('HistModel')->insert($operationLog);
        return $data;
    }

    protected function getUpdateLog(array $data): OperationLog
    {
        $old = $this->find($data['id']);
        $new = $data['data'];
        $old = $old->toRawArray();
        unset_array_keys($old, $this->updateChangesIgnore);
        unset_array_keys($new, $this->updateChangesIgnore);
        $changes = $this->getChangedFields($old, $new);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $data['id'],
            'action' =>'update',
            'table' => $this->table
        ]);
        return $operationLog;
    }

    protected function getChangedFields(array $old, array $new): array
    {
        $changes = [];
        foreach ($new as $name=>$newValue){
            if (array_key_exists($name, $old) && ($old[$name] != $newValue)){
                $changes[$name] = $newValue;
            }
        }
        return $changes;
    }

    protected function addDeleteTimestamps(array $data): array
    {   
        if ($data['purge']){
            return $data;
        }
        $opTimestamp = service('time')->getRequestTimestamp()->format('Y-m-d H:i:s.u');
        $builder = $this->builder();
        $builder->set('deleted_at', $opTimestamp)
                ->set('updated_at', $opTimestamp)
                ->where('id', $data['id'])
                ->update();
        return $data;
    }

    protected function logDelete(array $data)
    {
        $operationLog = $this->getDeleteLog($data);
        model('HistModel')->insert($operationLog);
        return $data;
    }

    protected function getDeleteLog(array $data): OperationLog
    {
        $entity = $this->find($data['id'][0]);
        $changes = $entity->toRawArray();
        unset_array_keys($changes, $this->deleteChangesIgnore);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $data['id'][0],
            'action' =>'delete',
            'table' => $this->table
        ]);
        return $operationLog;

    }

    public function restore(int|string|array $id): bool|array
    {
        if (is_array($id)){
            foreach($id as $entryId){
                $res[] = $this->restore($entryId);
            }
            return $res;
        }
        $data = ['id' => $id];
        foreach($this->beforeRestore as $callback){
            $data = $this->$callback($data);
        }
        $opTimestamp = service('time')->getRequestTimestamp()->format('Y-m-d H:i:s.u');
        $entry = $this->onlyDeleted()->select('id')->find($id);
        if (is_null($entry)){
            throw new DatabaseException('The entry is not deleted to be restored');
        }
        $builder = $this->builder();
        $builder->set('deleted_at', null)
                ->set('updated_at', $opTimestamp)
                ->where('id', $id)
                ->update();
        $data = ['id' => $id];
        foreach($this->afterRestore as $callback){
            $data = $this->$callback($data);
        }
        $this->resetQuery();
        return true;
    }

    protected function logRestore(array $data): array
    {
        $operationLog = $this->getRestoreLog($data);
        model('HistModel')->insert($operationLog);
        return $data;
    }

    protected function getRestoreLog(array $data): OperationLog
    {
        $operationLog = new OperationLog([
            'changes' => [],
            'record_id' => $data['id'],
            'action' =>'restore',
            'table' => $this->table
        ]);
        return $operationLog;
    }
}
