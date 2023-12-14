<?php

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
    protected string $opTimestamp;

    public function initialize()
    {
        helper('app_array');
        parent::initialize();
        $this->opTimestamp = service('time')->getRequestTimestamp()->format('Y-m-d H:i:s.u');
        // $this->beforeInsert[] = 'addInsertTimestamps';
        $this->beforeInsert[] = 'addUser';
        // $this->beforeUpdate[] = 'addUpdateTimestamps';
        $this->beforeUpdate[] = 'addUser';
        $this->beforeDelete[] = 'customDelete';
    }

    protected function addInsertTimestamps(array $data): array
    {
        $data['data']['created_at'] = $this->opTimestamp;
        $data['data']['updated_at'] = $this->opTimestamp;
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
        $data['data']['updated_at'] = $this->opTimestamp;
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
            ->get()->getResultArray();
        $entityData = $entityData[0];
        $entityData['table'] = $this->table;
        return $entityData;
    }

    protected function customDelete(array $data): array
    {   
        if ($data['purge']){
            return $data;
        }
        $username = service('authenticator')->getUser()->username;
        $builder = $this->builder();
        $builder->set('deleted_at', $this->opTimestamp)
                ->set('updated_at', $this->opTimestamp)
                ->set('user', $username)
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
        $entry = $this->onlyDeleted()->select('id')->find($id);
        if (is_null($entry)){
            throw new DatabaseException('The entry is not deleted to be restored');
        }
        $username = service('authenticator')->getUser()->username;
        $builder = $this->builder();
        $builder->set('deleted_at', null)
                ->set('updated_at', $this->opTimestamp)
                ->set('user', $username)
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

    protected function addUser(array $data): array
    {
        $data['data']['user'] = service('authenticator')->getUser()->username;
        return $data;
    }
}
