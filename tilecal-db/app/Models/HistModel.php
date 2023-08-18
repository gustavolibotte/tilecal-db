<?php

namespace App\Models;

class HistModel extends BaseModel
{
    protected $table            = 'hist';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\OperationLog::class;
    protected $allowedFields    = ['table', 'action', 'record_id', 'changes', 'filedata'];

    // Callbacks
    protected $beforeInsert   = ['addUser'];

    public function initialize()
    {
        helper('authentication');
    }

    public function addUser($operationInfo){
        $operationInfo['data']['user'] = ($this->isMigration)? 'root': getUser()->username;
        return $operationInfo;
    }

    public function getEquipmentHist($equipmentId){
        $hist = $this->select("hist.id, hist.user, hist.table, hist.action, hist.record_id, hist.changes, hist.created_at")
                     ->where("(table = 'equipments' AND record_id = '{$equipmentId}') OR (table = 'equipments_to_files' AND equipments_to_files.equipment_id = '{$equipmentId}')")
                     ->join("equipments_to_files", "hist.record_id = equipments_to_files.id", 'left')
                     ->join("equipments", "equipments.id = equipments_to_files.equipment_id", 'left')
                     ->orderBy('hist.created_at', 'DESC')
                     ->findAll();
        return $hist;
    }

    public function getFileHist($fileId){
        $fileId = $this->db->escape($fileId);
        $hist = $this->select("hist.id, hist.user, hist.table, hist.action, hist.record_id, hist.changes, hist.created_at")
                     ->where("(table = 'files' AND record_id = {$fileId}) OR (table = 'equipments_to_files' AND equipments_to_files.file_id = {$fileId})")
                     ->join("equipments_to_files", "hist.record_id = equipments_to_files.id", 'left')
                     ->join("files", "files.id = equipments_to_files.file_id", 'left')
                     ->orderBy('hist.created_at', 'DESC')
                     ->findAll();
        return $hist;
    }
}
