<?php

namespace App\Models;

use CodeIgniter\Entity\Entity;

class HistModel extends BaseModel
{
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\OperationLog::class;
    protected $protectFields = false;
    protected $modelAllowedFields;

    // Callbacks
    protected $beforeInsert   = ['addUser', 'addCreatedAt'];

    public function initialize()
    {
        helper(['authentication', 'app_array']);
    }

    protected function addUser($data){
        $data['user'] = service('authenticator')->getUser()->username;
        return $data;
    }

    protected function addCreatedAt($data){
        $data['created_at'] = service('time')->getRequestTimestamp()->format('Y-m-d H:i:s.u');
        return $data;
    }

    public function insert($data = null, bool $returnID = true)
    {
        if ($data instanceof Entity){
            $data = $data->toRawArray();
        }
        remove_values(['created_at', 'updated_at', 'deleted_at'], $data, false);
        $data['record_id'] = remove_value('id', $data);
        $table = remove_value('table', $data);
        $data = $this->addUser($data);
        $data = $this->addCreatedAt($data);
        $builder = $this->db->table(sprintf('%s_hist', $table));
        $builder->insert($data);
        if ($returnID){
            return $data['record_id'];
        }
    }

    public function getEquipmentHist($equipmentId){
        $hist = $this
        ->select("hist.id, hist.user, hist.table, hist.action, hist.record_id, hist.changes, hist.created_at")
        ->orWhere("table = 'equipments' AND record_id = '{$equipmentId}'")
        ->orWhere(sprintf(
            'table = %s AND JSON_EXTRACT(changes, "$.equipment_id") = %s',
            $this->db->escape('equipments_to_files'),
            $this->db->escape($equipmentId)
        ))
        ->orWhere(sprintf(
            'table = %s AND (JSON_EXTRACT(changes , "$.child_equip_id") = %s OR JSON_EXTRACT(changes , "$.parent_equip_id") = %s)' ,
            $this->db->escape('parent_child_equips'),
            $this->db->escape($equipmentId),
            $this->db->escape($equipmentId)
        ))
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
