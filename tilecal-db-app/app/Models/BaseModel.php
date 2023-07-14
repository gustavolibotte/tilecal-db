<?php

namespace App\Models;

use CodeIgniter\Model;

abstract class BaseModel extends Model{

    protected $histModelClass;
    protected $insertLogIgnore = [];
    protected $updateLogIgnore = [];
    protected $useInsertID = true;

    abstract protected function getUpdateLog($updateInfo);
    abstract protected function getInsertLog($insertInfo);

    protected function initialize(){
        $this->afterInsert[] = 'logInsert';
        $this->beforeUpdate[] = 'logUpdate';
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

}
