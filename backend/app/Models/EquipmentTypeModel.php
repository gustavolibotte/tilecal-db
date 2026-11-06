<?php

namespace App\Models;
use App\Entities\EquipmentType;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use TypeError;

class EquipmentTypeModel extends LogModel
{
    protected $table            = 'equipment_types';
    protected $useAutoIncrement = true;
    protected $returnType       = EquipmentType::class;
    protected $allowedFields    = [
        'name',
        'in_detector',
        'project',
        'system',
        'id_regex',
        'params',
        'active',
        'description',
        'slots'
    ];

    public function initialize(){
        parent::initialize();
    }

    public function insert($data = null, bool $returnID = true)
    {
        $this->db->transException(true)->transBegin();
        if (!($data instanceof EquipmentType)){
            throw new TypeError('Equipment Model insert supports only Equipment as $data');
        }
        $data = $data->toRawArray();
        $slots = remove_value('slots', $data, false);
        $equipmentTypeId = parent::insert($data, true);
        if (isset($equipmentTypeId) && isset($slots)){
            $typeSlotModel = model('TypeSlotModel');
            $slots = json_decode($slots, true);
            foreach ($slots as $slot){
                $slot = $this->substituteAllowedTypeIds($slot);
                $slot['parent_type_id'] = $equipmentTypeId;
                $slot = new $typeSlotModel->returnType($slot);
                $typeSlotModel->insert($slot);
            }
        }
        $this->db->transCommit();
        if ($returnID){
            return $equipmentTypeId;
        }
    }

    protected function updateSlots(array $data): array
    {
        helper('app_array');
        $slots = remove_value('slots', $data['data'], false);
        if (empty($slots)){
            return $data;
        }
        $typeSlotModel = model('TypeSlotModel');
        foreach ($slots as $slot){
            $slot = $this->substituteAllowedTypeIds($slot);
            $id = $typeSlotModel->where('name', $slot['name'])->first()->id;
            $typeSlotModel->update($id, $slot);
        }
        return $data;
    }

    protected function substituteAllowedTypeIds(array $slot): array
    {
        $name2Id = $this
            ->whereIn('name', $slot['allowed_type_ids'])
            ->getCustomArray('name', 'id');
        foreach($slot['allowed_type_ids'] as &$typeId){
            $typeId = $name2Id[$typeId];
        }
        return $slot;
    }
    
    public function joinWithGroupedTypeSlots(BaseBuilder|Model $queryBuilder=null): BaseBuilder|Model
    {
        if (is_null($queryBuilder)){
            $queryBuilder = $this;
        }

        $query = "(SELECT parent_type_id,
                    JSON_ARRAYAGG(name) AS slot_names,
                    JSON_ARRAYAGG(id) AS slot_id
                FROM type_slots ts 
                WHERE deleted_at IS NULL
                GROUP BY parent_type_id) grouped_type_slots";
        
        $queryBuilder = $queryBuilder->join(
            $query,
            'equipment_types.id=grouped_type_slots.parent_type_id',
            'LEFT'
        );
        return $queryBuilder;
    }
    
    /**
     * Returns true if the slot allows insertion of an equipment.
     *
     * @param string $slotName Name of the slot to check
     * @param string $id Id of the equipment to check
     * 
     * @return bool true if equipment is allowed in the slot
     */
    public function slotAllowsEquip(string $slotName, string $equipmentId, int $parentTypeId): bool
    {
        $res = $this->builder('type_slots ts')
            ->select('1')
            ->join('allowed_type_slots ats', 'ts.id=ats.slot_id', 'left')
            ->join('equipments e', 'ats.allowed_type_id=e.type_id')
            ->where('e.id', $equipmentId)
            ->where('ts.name', $slotName)
            ->where('ts.parent_type_id', $parentTypeId)
            ->where('ts.deleted_at', null)
            ->where('ats.deleted_at', null)
            ->get()->getFirstRow('array');
        return isset($res);
    }
}
