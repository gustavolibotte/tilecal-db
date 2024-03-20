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
        $this->beforeInsert[] = 'addInsertTimestamps';
        $this->beforeInsert[] = 'addUser';
        $this->beforeUpdate[] = 'addUpdateTimestamps';
        $this->beforeUpdate[] = 'addUser';
        $this->beforeDelete[] = 'customDelete';
    }

    protected function addInsertTimestamps(array $data): array
    {
        $data['data']['created_at'] = $this->opTimestamp;
        $data['data']['updated_at'] = $this->opTimestamp;
        return $data;
    }

    protected function addUpdateTimestamps(array $data): array
    {
        $data['data']['updated_at'] = $this->opTimestamp;
        return $data;
    }

    protected function customDelete(array $data): array
    {   
        if ($data['purge']){
            return $data;
        }
        $username = \Config\Services::user()->getUsername();
        $builder = $this->builder();
        $builder->set('deleted_at', $this->opTimestamp)
                ->set('updated_at', $this->opTimestamp)
                ->set('user', $username)
                ->where('id', $data['id'])
                ->update();
        return $data;
    }

    protected function addUser(array $data): array
    {
        $data['data']['user'] = \Config\Services::user()->getUsername();
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
        $username = \Config\Services::user()->getUsername();
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
}
