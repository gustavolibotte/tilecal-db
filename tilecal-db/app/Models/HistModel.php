<?php

namespace App\Models;

use App\Entities\LogEntities\EquipFileRelationLog;
use App\Entities\LogEntities\ParentChildRelationLog;
use App\Entities\LogEntities\EquipmentLog;
use App\Entities\LogEntities\FileLog;
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

    public function getEquipmentHist($id){
        $equipmentLogs = $this->db->table('equipments_hist eh')
            ->join('equipment_types et', 'eh.type_id = et.id', 'left')
            ->join('statuses s', 'eh.status_id=s.id', 'left')
            ->join('locations l', 'eh.location_id=l.id', 'left')
            ->join('institutes i', 'eh.owner_id=i.id', 'left')
            ->select("'equip' as hist_type")
            ->select('eh.record_id as id')
            ->select('et.label as type')
            ->select('s.name as status')
            ->select('l.name as location')
            ->select('i.name as owner')
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
            ->select('efh.file_id')
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
            ->select('name, description, user, action, created_at')
            ->where('record_id', $id)
            ->get()->getCustomResultObject(FileLog::class);
        $fileRelationLogs = $this->db->table('equipments_to_files_hist efh')
            ->join('files f', 'efh.file_id=f.id', 'left')
            ->select(
                'efh.file_id, ' .
                'f.name as file_name, ' .
                'efh.equipment_id, ' .
                'efh.action, ' .
                'efh.user, ' .
                'efh.created_at'
            )
            ->where('efh.file_id', $id)
            ->get()->getCustomResultObject(EquipFileRelationLog::class);
        $hist = array_merge($fileLogs, $fileRelationLogs);
        return $hist;
    }
}
