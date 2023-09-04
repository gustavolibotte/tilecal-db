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

    protected $useSoftDeletes = true;
    protected array $insertChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];
    protected array $updateChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];
    protected array $deleteChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];
    protected array $restoreChangesIgnore = ['updated_at', 'created_at', 'deleted_at'];
    protected array $insertWithLogCallbacks = []; 
    protected array $updateWithLogCallbacks = []; 
    protected array $deleteWithLogCallbacks = []; 
    protected array $restoreWithLogCallbacks = []; 

    public function initialize()
    {
        helper('app_array');
        parent::initialize();
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
        $histModel = new HistModel();
        if (!($data instanceof Entity)){
            throw new \TypeError('$data must be instance of Entity class');
        }
        $requestTimestamp = service('time')->getRequestTimestamp();
        $data->created_at = $requestTimestamp;
        $data->updated_at = $requestTimestamp;
        $res = $this->insert($data, $returnID);
        $id = $this->getInsertID();
        $operationLog = $this->getInsertLog($id, $data);
        $histModel->insert($operationLog);
        $this->callInsertCallbacks($id, $data, $operationLog);
        return $res;
    }

    protected function callInsertCallbacks(mixed $id, Entity $data, OperationLog $operationLog){
        foreach($this->insertWithLogCallbacks as $callback){
            $this->$callback($id, $data, $operationLog);
        }
    }

    protected function getInsertLog(int|string $id, Entity $data): OperationLog|array
    {
        $changes = $data->toArray();
        unset_array_keys($changes, $this->insertChangesIgnore);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'insert',
            'table' => $this->table
        ]);
        return $operationLog;
    }

    public function updateWithLog(int|string|array $id, $data = null): bool
    {
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->updateWithLog($sampleId, $data);
            }
            return $res;
        }
        $histModel = new HistModel();
        if (!($data instanceof Entity)){
            throw new \TypeError('$data must be instance of Entity class');
        }
        $data->updated_at = service('time')->getRequestTimestamp();
        $old = $this->find($id);
        $operationLog = $this->getUpdateLog($id, $data, $old);
        $res = $this->update($id, $data);
        $histModel->insert($operationLog);
        $this->callUpdateCallbacks($id, $data, $operationLog);
        return $res;
    }

    protected function callUpdateCallbacks(mixed $id, Entity $data, OperationLog $operationLog){
        foreach($this->updateWithLogCallbacks as $callback){
            $this->$callback($id, $data, $operationLog);
        }
    }

    protected function getUpdateLog(int|string $id, Entity $new,  Entity $old):OperationLog| array
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
            'table' => $this->table
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

    public function deleteWithLog(int|string|array $id = null, bool $purge = false)
    {
        
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->deleteWithLog($sampleId, $purge);
            }
            return $res;
        }
        $histModel = new HistModel();
        $callbackData['operationLog'] = $this->getDeleteLog($id);
        $callbackData['deleted_at'] = service('time')->getRequestTimestamp();
        $callbackData['id'] = $id;
        $res = $this->update($id, ['deleted_at' => $callbackData['deleted_at']]);
        $histModel->insert($callbackData['operationLog']);
        $this->callDeleteCallbacks($callbackData);
        return $res;
    }

    protected function callDeleteCallbacks(array &$callbackData){
        foreach($this->deleteWithLogCallbacks as $callback){
            echo "Calling {$callback}";
            $this->$callback($callbackData);
        }
    }

    protected function getDeleteLog(int|string $id): OperationLog|array
    {
        $changes = $this->find($id)->toArray();
        unset_array_keys($changes, $this->deleteChangesIgnore);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'delete',
            'table' => $this->table
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
        $this->restore($id);
        $histModel->insert($operationLog);
        $this->callRestoreCallbacks($id, $operationLog);

        return true;
    }

    protected function callRestoreCallbacks(mixed $id, OperationLog $operationLog){
        foreach($this->restoreWithLogCallbacks as $callback){
            $this->$callback($id, $operationLog);
        }
    }

    public function restore(int|string|array $id): bool
    {
        $this->onlyDeleted()->update($id, ['deleted_at' => null, 'updated_at' => service('time')->getRequestTimestamp()]);
        return true;
    }

    protected function getRestoreLog(int|string $id, Entity $data): OperationLog|array
    {
        $changes = $data->toArray();
        unset_array_keys($changes, $this->restoreChangesIgnore);
        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'restore',
            'table' => $this->table
        ]);
        return $operationLog;
    }
}
