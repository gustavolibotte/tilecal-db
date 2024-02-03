<?php

namespace App\Models;

use CodeIgniter\Entity\Entity;
use App\Entities\OperationLog;

class HistModel extends BaseModel
{
    protected $useAutoIncrement = true;
    protected $returnType       = OperationLog::class;
    protected $protectFields = false;
    protected $modelAllowedFields;

    public function initialize()
    {
        helper(['authentication', 'app_array']);
    }

    public function getEquipmentHist($id){
        $equipmentLogs = $this->db->table('equipments_hist eh')
            ->join('equipment_types et', 'eh.type_id = et.id', 'left')
            ->join('statuses s', 'eh.status_id=s.id', 'left')
            ->join('locations l', 'eh.location_id=l.id', 'left')
            ->join('institutes i', 'eh.owner_id=i.id', 'left')
            ->select("'equip' as hist_type")
            ->select('eh.record_id as id')
            ->select('et.name as type_name')
            ->select('s.name as status_name')
            ->select('l.name as location_name')
            ->select('i.name as owner_name')
            ->select('eh.serial')
            ->select('eh.type_params')
            ->select('eh.comments')
            ->select('eh.action')
            ->select('eh.user')
            ->select('eh.created_at')
            ->where('eh.record_id', $id)
            ->get()->getResultArray();
        array_walk($equipmentLogs, function (&$value, $key){
            $value['type_params'] = json_decode($value['type_params'], true);
        });
        $equipmentRelationLogs = $this->db->table('parent_child_equips_hist pceh')
            ->select("'parent_rel' as hist_type")
            ->select('pceh.parent_equip_id')
            ->select('pceh.child_equip_id')
            ->select('pceh.action')
            ->select('pceh.user')
            ->select('pceh.created_at')
            ->orWhere('pceh.parent_equip_id', $id)
            ->orwhere('pceh.child_equip_id', $id)
            ->get()->getResultArray();
        $fileRelationLogs = $this->db->table('equipments_to_files_hist efh')
            ->join('files f', 'efh.file_id=f.id', 'left')
            ->select("'file_rel' as hist_type")
            ->select('f.id as file_id')
            ->select('f.name as file_name')
            ->select('efh.equipment_id')
            ->select('efh.action')
            ->select('efh.user')
            ->select('efh.created_at')
            ->where('efh.equipment_id', $id)
            ->get()->getResultArray();
        $hist = array_merge(
            $equipmentLogs,
            $equipmentRelationLogs,
            $fileRelationLogs
        );
        return $hist;
    }

    public function getFileHist(int $id){
        $fileLogs = $this->db->table('files_hist fh')
            ->select("fh.id")
            ->select("'file' as hist_type")
            ->select('name')
            ->select('description')
            ->select('user')
            ->select('action')
            ->select('created_at')
            ->where('record_id', $id)
            ->get()->getResultArray();
        $fileRelationLogs = $this->db->table('equipments_to_files_hist efh')
            ->join('files f', 'efh.file_id=f.id', 'left')
            ->select("'file_rel' as hist_type")
            ->select('efh.file_id')
            ->select('f.name as file_name')
            ->select('efh.equipment_id')
            ->select('efh.action')
            ->select('efh.user')
            ->select('efh.created_at')
            ->where('efh.file_id', $id)
            ->get()->getResultArray();
        $hist = array_merge($fileLogs, $fileRelationLogs);
        return $hist;
    }

    public function getOlderFile(int $histId)
    {
        return $this->db
            ->table('files_hist fh')
            ->where('id', $histId)
            ->get()->getResultArray()[0];
    }
}
