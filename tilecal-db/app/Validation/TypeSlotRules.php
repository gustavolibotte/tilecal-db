<?php

namespace App\Validation;

class TypeSlotRules
{
    /**
     * Returns true if the slot allows fit of a certain type
     *
     * @param $value  slot id given by codeigniter validator
     * @param string $params Type id to check
     * @param array $data Ignored, here for compatibility with codeigniter validation api
     * @param ?string &$error Ignored, here for compatibility with codeigniter validation api
     * 
     * @return bool true if slot allows type id fit
     */
    public function slot_allows_type($value, string $params, array $data, ?string &$error): bool
    {
        $slotId = $value;
        $typeId = $params;
        $slot = model('TypeSlotModel')
            ->select('1')
            ->whereAllowedTypeId($typeId)
            ->find($slotId);
        return isset($slot);
    }

    /**
     * Returns true if the slot allows fit of a equipment child_id into parent_id
     *
     * @param $value  slot id given by codeigniter validator
     * @param string $params <parent_id>,<child_id>
     * @param array $data Ignored, here for compatibility with codeigniter validation api
     * @param ?string &$error Constructed automatically by the rule
     * 
     * @return bool true if slot allows equipment id fitting
     */
    public function slot_is_available($value, string $params, array $data, ?string &$error): bool
    {
        $params = explode(',', $params);
        $parentId = $params[0];
        $childId = $params[1];
        $slotId = $value;

        $equipmentModel = model('EquipmentModel');
        if (!$equipmentModel->equipHasSlot($parentId, $slotId)){
            $error = sprintf(
                'Equipment %s doesn\'t have the selected slot',
                $parentId
            );
            return false;
        }

        if (!$equipmentModel->equipSlotIsEmpty($parentId, $slotId)){
            $error = sprintf(
                'Selected slot from Equipment %s is in use.',
                $parentId
            );
            return false;
        }

        if (!model('TypeSlotModel')->slotAllowsEquip($slotId, $childId)){
            $error = sprintf(
                'Slot is available but does not allow insertion of equipment %s',
                $childId
            );
            return false;
        }

        return true;
    }
}
