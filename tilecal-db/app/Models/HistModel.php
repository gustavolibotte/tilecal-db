<?php

namespace App\Models;

class HistModel extends BaseModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'hist';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\OperationLog::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['table', 'action', 'record_id', 'changes', 'filedata'];

    // Dates
    protected $useTimestamps = false;
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
    protected $beforeInsert   = ['addUser'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function initialize()
    {
        helper('authentication');
    }

    public function addUser($operationInfo){
        $operationInfo['data']['user'] = ($this->isMigration)? 'root': getUser()->username;
        return $operationInfo;
    }

    public function getEquipmentHist($equipmentId){
        $equipmentHist = $this->where("table = 'equipments'")
                       ->where(sprintf("record_id = %s", $equipmentId))
                       ->findAll();
        $fileLinkHist = $this->where("table = 'equipments_to_files'")
                             ->where(sprintf('JSON_EXTRACT(changes, "$.equipment_id") = %s', $equipmentId))
                             ->findAll();
        $hist = array_merge($equipmentHist, $fileLinkHist);
        usort($hist, [$this->returnType, "orderByCreationReverse"]);
        return $hist;
    }

    public function getFileHist($fileId){
        $fileHist = $this->where("table = 'equipments'")
                         ->where(sprintf("record_id = %s", $fileId))
                         ->findAll();
        $equipLinkHist = $this->where("table = 'equipments_to_files'")
                             ->where(sprintf('JSON_EXTRACT(changes, "$.file_id") = %s', $fileId))
                             ->findAll();
        $hist = array_merge($fileHist, $equipLinkHist);
        usort($hist, [$this->returnType, "orderByCreationReverse"]);
        return $hist;
    }
}
