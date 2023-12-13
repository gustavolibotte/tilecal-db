<?php

namespace App\Models;
use App\Entities\EquipmentType;
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
        'label',
        'params',
        'active',
        'description',
        'slots'
    ];

    public function initialize(){
        parent::initialize();
        // $this->beforeUpdate[] = 'updateSlots';
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
}
