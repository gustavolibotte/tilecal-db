<?php

namespace App\Models;

use App\Entities\OperationLog;

class FileModel extends LogModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'files';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\File::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'mime', 'filedata', 'description'];

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
    protected $insertLogIgnore = ['created_at', 'updated_at', 'deleted_at', 'filedata'];
    protected $updateLogIgnore = ['created_at', 'updated_at', 'deleted_at', 'filedata'];

    public function getEquipmentRelated($id){
        $query = $this->join('equipments_to_files', 'files.id = equipments_to_files.file_id')
                      ->where(sprintf("equipments_to_files.equipment_id = '%s'", $id));
        $result = $query->findAll();
        return $result;
    }

    public function getFile($id=null, $columns=null, $searchDeleted=false)
    {
        if (is_null($columns)){
            $columns = 'id, name, description, created_at, updated_at, deleted_at';
        }
        $query = $this->select($columns);
        
        if (is_null($id)){
            $query = $this->searchDeleted($query, $searchDeleted);
            $result = $query->findAll();
        }
        else{
            $result = $query->withDeleted()->find($id);
        }

        return $result;
    }

    protected function searchDeleted($query, $searchDeleted){
        if ($searchDeleted){
            return $query->withDeleted()->where("files.deleted_at IS NOT NULL");
        }
        return $query;
    }

    protected function getUpdateLog($updateInfo){
        $id = $updateInfo["id"][0];
        $oldRow = $this->find($id)->toRawArray();
        $changes = $this->getUpdateChanges($oldRow, $updateInfo["data"]);
        $updateLog = new OperationLog([
            "record_id" => $id,
            "action" => "update",
            "changes" => $changes,
            "filedata" => $updateInfo["data"]["filedata"]
        ]);
        return $updateLog;
    }

    protected function getInsertLog($insertInfo){
        $changes = [];
        foreach ($insertInfo['data'] as $name=>$value){
            if (!in_array($name, $this->insertLogIgnore)){
                $changes[$name] = $value;
            }
        }
        $insertLog = new OperationLog([
            "record_id" => $insertInfo["id"],
            "action" => "insert",
            "changes" => $changes,
            "filedata" => $insertInfo["data"]["filedata"]
        ]);

        return $insertLog;
    }
}
