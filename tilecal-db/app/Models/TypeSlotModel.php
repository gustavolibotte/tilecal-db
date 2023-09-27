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

    public function hasSlots(string $typeName){
        $result = $this
            ->select('1')
            ->where(sprintf(
                '(SELECT id FROM equipment_types WHERE name=\'%s\') MEMBER OF(type_ids_fit)',
                $typeName,
            ), escape: false)
            ->first();
        return !empty($result);
    }

    public function insert($data = null, bool $returnID = true)
    {
        $this->db->transStart();
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
        $this->transComplete();
        if ($returnID){
            return $slotId;
        } else{
            return null;
        }
    }
}
