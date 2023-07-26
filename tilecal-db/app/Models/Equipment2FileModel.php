<?php

namespace App\Models;

use App\Entities\OperationLog;

class Equipment2FileModel extends LogModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'equipments_to_files';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       =  \App\Entities\Equipment2File::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['equipment_id', 'file_id'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
    
    // Custom
    protected $histModelClass = Equipment2FileHistModel::class;

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

    protected function getDeleteLog($deleteInfo){
        $equipment2FileModel = new Equipment2FileModel();
        $data2Delete = $equipment2FileModel
            ->find($deleteInfo["id"])
            ->toRawArray();
        $deleteLog = new OperationLog([
            "record_id" => $deleteInfo["id"],
            "action" => "delete",
            "changes" => json_encode($data2Delete)
        ]);
        $histModel = new $this->histModelClass();
        $histModel->insert($deleteLog);

        return $deleteInfo;
    }
}
