<?php

namespace App\Models;

use App\Entities\OperationLog;

abstract class LogModel extends BaseModel{

    protected $table;
    protected bool $isMigration = false;
    protected $insertLogIgnore = ['created_at', 'updated_at', 'deleted_at'];
    protected $updateLogIgnore = ['created_at', 'updated_at', 'deleted_at'];
    protected $deleteLogIgnore = ['created_at', 'updated_at', 'deleted_at', 'filedata'];

    public function __construct(bool $isMigration=false)
    {   
        parent::__construct();
        $this->isMigration = $isMigration;
    }

    protected function initialize(){
        parent::initialize();
        $this->afterInsert[] = 'logInsert';
        $this->beforeUpdate[] = 'logUpdate';
        $this->beforeDelete[] = 'logDelete';
    }

    protected function logInsert($insertInfo){
        $insertLog = $this->getInsertLog($insertInfo);
        $insertLog->table = $this->table;
        $histModel = new HistModel($this->isMigration);
        $histModel->insert($insertLog);
        return $insertInfo;
    }

    protected function logUpdate($updateInfo){
        $updateLog = $this->getUpdateLog($updateInfo);
        $updateLog->table = $this->table;
        $histModel = new HistModel($this->isMigration);
        $histModel->insert($updateLog);
        return $updateInfo;
    }

    protected function getUpdateChanges(array $old, array $new){
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
            return [];
        }
    }

    protected function getUpdateLog($updateInfo){
        $id = $updateInfo["id"][0];
        $oldRow = $this->find($id)->toArray();
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
        $histModel = new HistModel($this->isMigration);
        foreach ($this->find($deleteInfo['id']) as $entity){
            $changes = array();
            foreach ($entity->toRawArray() as $name=>$value){
                if (!in_array($name, $this->deleteLogIgnore)){
                    $changes[$name] = $value;
                }
            }
            $deleteLog = new OperationLog([
                "record_id" => $entity->id,
                "action" => "delete",
                "changes" => $changes,
                "table" => $this->table
            ]);
            $histModel->insert($deleteLog);
        }

        return $deleteInfo;
    }
}
