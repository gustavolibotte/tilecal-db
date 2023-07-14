<?php

namespace App\Models;

use App\Entities\OperationLog;
use CodeIgniter\Model;

abstract class LogModel extends Model{

    protected $histModelClass;
    protected $insertLogIgnore = ['created_at', 'updated_at', 'deleted_at'];
    protected $updateLogIgnore = ['created_at', 'updated_at', 'deleted_at'];
    abstract protected function getUpdateLog($updateInfo);
    abstract protected function getInsertLog($insertInfo);

    protected function initialize(){
        $this->afterInsert[] = 'logInsert';
        $this->beforeUpdate[] = 'logUpdate';
        $this->beforeDelete[] = 'logDelete';
    }

    protected function logInsert($insertInfo){
        $insertLog = $this->getInsertLog($insertInfo);
        $histModel = new $this->histModelClass();
        $histModel->insert($insertLog);
        return $insertInfo;
    }

    protected function logUpdate($updateInfo){
        $updateLog = $this->getUpdateLog($updateInfo);
        $histModel = new $this->histModelClass();
        $histModel->insert($updateLog);
        return $updateInfo;
    }

    protected function getUpdateChanges($old, $new){
        $changes = [];
        foreach ($new as $name=>$newValue){
            if ((!in_array($name, $this->updateLogIgnore)) and ($old[$name] != $newValue)){
                $changes[$name] = [
                    "old" => $old[$name],
                    "new" => $newValue
                ];
            }
        }
        return $changes;
    }

    protected function logDelete($deleteInfo){
        $deleteLog = new OperationLog([
            "record_id" => $deleteInfo["id"],
            "action" => "delete",
            "user" => "root",
            "changes" => [] // No need to register changes since the app uses soft delete
        ]);
        $histModel = new $this->histModelClass();
        $histModel->insert($deleteLog);

        return $deleteInfo;
    }
}
