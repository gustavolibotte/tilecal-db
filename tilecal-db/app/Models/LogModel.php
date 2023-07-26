<?php

namespace App\Models;

use App\Entities\OperationLog;
use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

abstract class LogModel extends Model{

    protected $histModelClass;
    protected bool $migration;
    protected $insertLogIgnore = ['created_at', 'updated_at', 'deleted_at'];
    protected $updateLogIgnore = ['created_at', 'updated_at', 'deleted_at'];
    abstract protected function getUpdateLog($updateInfo);
    abstract protected function getInsertLog($insertInfo);

    public function __construct(bool $migration=false)
    {   
        parent::__construct();
        $this->migration = $migration;
    }

    protected function initialize(){
        helper('auhtentication');
        $this->afterInsert[] = 'logInsert';
        $this->beforeUpdate[] = 'logUpdate';
        $this->beforeDelete[] = 'logDelete';
    }

    protected function getUsername(){
        if ($this->migration){
            return 'root';
        }else{
            return getUser()->username;
        }
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
