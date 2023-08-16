<?php

namespace App\Models;

use App\Entities\OperationLog;
use CodeIgniter\I18n\Time;
use CodeIgniter\Entity\Entity;

abstract class LogModel extends BaseModel{

    protected bool $isMigration = false;
    protected $insertChangesIgnore = ['created_at', 'updated_at'];
    protected $updateChangesIgnore = ['created_at', 'updated_at'];
    protected $deleteChangesIgnore = ['created_at', 'updated_at'];

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

    public function insert($data = null, bool $returnID = true)
    {
        $histModel = new HistModel();
        $opTimestamp = Time::now();
        if (!($data instanceof Entity)){
            throw new \TypeError('$data must be instance of Entity class');
        }
        $data->created_at = $opTimestamp;
        $data->updated_at = $opTimestamp;
        $this->db->transStart();
        $res = parent::insert($data, $returnID);
        $id = $this->getInsertID();
        $operationLog = new OperationLog([
            'changes' => $this->getInsertChanges($id, $data),
            'id' => $id,
            'action' =>'delete',
            'table' => $this->table,
            'created_at' => $opTimestamp
        ]);
        $histModel->insert($operationLog);
        $this->db->transComplete();
        return $res;
    }

    protected function getInsertChanges(int|string $id, Entity $data): array
    {
        $changes = $data->toArray();
        $changes['id'] = $id;
        unset_array_keys($changes, $this->insertChangesIgnore);
        return $changes;
    }

    public function update($id = null, $data = null): bool
    {
        $histModel = new HistModel();
        $opTimestamp = Time::now();
        if (!($data instanceof Entity)){
            throw new \TypeError('$data must be instance of Entity class');
        }
        $data->updated_at = $opTimestamp;
        $operationLog = new OperationLog([
            'changes' => $this->getUpdateChanges($id, $data),
            'record_id' => $id,
            'action' =>'delete',
            'table' => $this->table,
            'created_at' => $opTimestamp
        ]);
        $this->db->transStart();
        $res = parent::update($id, $data);
        $histModel->insert($operationLog);
        $this->db->transComplete();
        return $res;
    }

    protected function getUpdateChanges(int|string $id, Entity $data): array
    {
        $new = $data->toArray();
        $old = $this->find($id)->toArray();
        unset_array_keys($new, $this->updateChangesIgnore);
        $changes = $this->getChangedFields($old, $new);
        return $changes;
    }

    protected function getChangedFields(array $old, array $new): array
    {
        foreach ($new as $name=>$newValue){
            if (($old[$name] != $newValue)){
                $changes[$name] = $newValue;
            }
        }
        return (isset($changes))? $changes: [];
    }

    public function delete($id = null, bool $purge = false)
    {   
        $histModel = new HistModel();
        $opTimestamp = Time::now();
        $data = $this->find($id);
        $data->deleted_at = $opTimestamp;
        $operationLog = new OperationLog([
            'changes' => $this->getDeleteChanges($id),
            'record_id' => $id,
            'action' =>'delete',
            'table' => $this->table,
            'created_at' => $opTimestamp
        ]);
        $this->db->transStart();
        $res = parent::delete($id, $purge);
        $histModel->insert($operationLog);
        $this->db->transComplete();
        return $res;
    }

    protected function getDeleteChanges(int|string $id): array
    {
        return $this->find($id)->toArray();

    }

    public function restore(int|string $id): bool
    {
        $histModel = new HistModel();
        $opTimestamp = Time::now();
        $operationLog = new OperationLog([
            'changes' => $this->getRestoreChanges($id),
            'record_id' => $id,
            'action' =>'delete',
            'table' => $this->table,
            'created_at' => $opTimestamp
        ]);
        $this->db->transStart();
        $this->withDeleted()->update($id, ['deleted_at' => null]);
        $histModel->insert($operationLog);
        $this->db->transComplete();

        return true;
    }

    protected function getRestoreChanges(int|string $id): array
    {
        return [];
    }
}
