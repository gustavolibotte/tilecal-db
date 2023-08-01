<?php

namespace App\Models;

use App\Entities\OperationLog;
use CodeIgniter\Model;

abstract class LogModel extends Model{

    protected $histModelClass;
    protected bool $isMigration = false;
    protected $insertLogIgnore = ['created_at', 'updated_at', 'deleted_at'];
    protected $updateLogIgnore = ['created_at', 'updated_at', 'deleted_at'];

    public function __construct(bool $isMigration=false)
    {   
        parent::__construct();
        $this->isMigration = $isMigration;
    }

    protected function initialize(){
        helper('auhtentication');
        // $this->afterInsert[] = 'logInsert';
        // $this->beforeUpdate[] = 'logUpdate';
        // $this->beforeDelete[] = 'logDelete';
    }

    protected function logInsert($insertInfo){
        $insertLog = $this->getInsertLog($insertInfo);
        $insertLog->user = $this->getUsername();
        $histModel = new $this->histModelClass();
        $histModel->insert($insertLog);
        return $insertInfo;
    }

    protected function logUpdate($updateInfo){
        $updateLog = $this->getUpdateLog($updateInfo);
        $updateLog->user = $this->getUsername();
        $histModel = new $this->histModelClass();
        $histModel->insert($updateLog);
        return $updateInfo;
    }

    protected function getUpdateChanges($old, $new){
        foreach ($new as $name=>$newValue){
            if ((!in_array($name, $this->updateLogIgnore)) and ($old[$name] != $newValue)){
                $changes[$name] = [
                    "old" => $old[$name],
                    "new" => $newValue
                ];
            }
        }
        if (isset($changes)){
            return $changes;
        }else{
            return null;
        }
    }

    protected function getUpdateLog($updateInfo){
        $id = $updateInfo["id"][0];
        $oldRow = $this->find($id)->toRawArray();
        $changes = $this->getUpdateChanges($oldRow, $updateInfo["data"]);
        $updateLog = new OperationLog([
            "record_id" => $id,
            "action" => "update",
            "changes" => $changes
        ]);
        return $updateLog;
    }

    protected function getInsertLog($insertInfo){
        foreach ($insertInfo['data'] as $name=>$value){
            if (!in_array($name, $this->insertLogIgnore)){
                $changes[$name] = $value;
            }
        }
        $insertLog = new OperationLog([
            "record_id" => $insertInfo["id"],
            "action" => "insert",
            "changes" => $changes
        ]);

        return $insertLog;
    }

    protected function logDelete($deleteInfo){
        helper('authentication');
        $deleteLog = new OperationLog([
            "record_id" => $deleteInfo["id"],
            "action" => "delete",
            "user" => $this->getUsername(),
            "changes" => null // No need to register changes since the app uses soft delete
        ]);
        $histModel = new $this->histModelClass();
        $histModel->insert($deleteLog);

        return $deleteInfo;
    }
}
