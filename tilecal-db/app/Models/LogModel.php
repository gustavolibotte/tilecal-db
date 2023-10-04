<?php

// This model re implements CRUD with trans methods so all database operations are made on the same transaction
// This is important since we are writing in more than one table
// This also ensures that each operation timestamps are equal

namespace App\Models;

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
        $entityData = $data['data'];
        $entityData['table'] = $this->table;
        $entityData['id'] = $data['id'];
        $entityData['action'] = 'insert';
        model('HistModel')->insert($entityData);
        return $data;
    }

    protected function addUpdateTimestamps(array $data): array
    {
        $data['data']['updated_at'] = service('time')->getRequestTimestamp()->format('Y-m-d H:i:s.u');
        return $data;
    }

    protected function logUpdate(array $data)
    {
        $logData = $this->queryLogData($data['id'][0]);
        $logData['action'] = 'update';
        model('HistModel')->insert($logData);
        return $data;
    }

    protected function queryLogData(string|int $id)
    {
        $entityData = $this->db->table($this->table)
            ->where('id', $id)
            ->get()->getResultArray()[0];
        $entityData['table'] = $this->table;
        return $entityData;
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
        $logData = $this->queryLogData($data['id'][0]);
        $logData['action'] = 'delete';
        model('HistModel')->insert($logData);
        return $data;
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
        $logData = $this->queryLogData($data['id']);
        $logData['action'] = 'restore';
        model('HistModel')->insert($logData);
        return $data;
    }
}
