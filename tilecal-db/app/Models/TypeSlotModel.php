<?php

namespace App\Models;

use App\Entities\TypeSlot;
use TypeError;

class TypeSlotModel extends LogModel
{
    protected $table            = 'type_slots';
    protected $useAutoIncrement = true;
    protected $returnType       = TypeSlot::class;
    protected $allowedFields    = ['name', 'parent_type_id', 'allowed_type_ids'];

    /**
     * True if the given typeName can be fit in some other equipment type slot
     *
     * @param string   $typeName  Name of the equipment type name to check for slots
     *
     * @return bool True if is possible to fit the given equipment type in some slot
     */
    public function hasSlots(string $typeName){
        $result = $this
            ->select('1')
            ->join('allowed_type_slots ats', 'type_slots.id=ats.slot_id', 'left')
            ->join('equipment_types et', 'ats.allowed_type_id=et.id')
            ->where('et.name', $typeName)
            ->first();
        return !empty($result);
    }

    public function insert($data = null, bool $returnID = true)
    {
        $this->db->transBegin();
        helper('app_array');
        if (!($data instanceof TypeSlot)){
            throw new TypeError('Can only insert TypeSlot instances.');
        }
        if (is_array($data)){
            foreach ($data as $sample){
                $res[] = $this->insert($sample);
            }
            return $res;
        }
        $data = $data->toRawArray();
        $allowedTypeIds = json_decode(
            remove_value_with_default('allowed_type_ids', $data, '[]'),
            true
        );
        $slotId = parent::insert($data, true);
        $allowdTypeSlotModel = model('AllowedTypeSlotModel');
        foreach($allowedTypeIds as $typeId){
            $allowdTypeSlotModel->insert([
                'slot_id' => $slotId,
                'allowed_type_id' => $typeId
            ]);
        }
        $this->db->transCommit();
        if ($returnID){
            return $slotId;
        } else{
            return null;
        }
    }

    public function whereAllowedTypeId(int $id): Self
    {
        $this->join('allowed_type_slots ats', 'type_slots.id=ats.slot_id', 'left')
            ->where('ats.allowed_type_id', $id);
        return $this;
    }
}
