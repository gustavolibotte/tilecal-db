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
}
