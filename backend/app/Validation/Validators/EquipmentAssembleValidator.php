<?php

namespace App\Validation\Validators;
use App\Validation\AtlasIdRules;

class EquipmentAssembleValidator extends EntryValidator
{

    public function initialize()
    {
        parent::initialize();
        $this->validationRules['parent_id'] = [
            "rules" => [
                "required",
                "equipment_id_is_sanitized",
                "is_not_unique[equipments.id]"
            ],
            "errors" => [
                "required" => "You must supply an id for the main equipment.",
                "is_not_unique" => "ID {value} doesn't exist."
            ]
        ];

        $this->validationRules['child_id'] = [
            "rules" => [
                "required",
                "equipment_id_is_sanitized",
                "is_not_unique[equipments.id]",
                "equip_is_not_assembled_into"
            ],
            "errors" => [
                "required" => "You must supply an id for the external equipment.",
                "exact_length" => "The ID sequential number must have {param} characters. Value given: {value}",
                "is_not_unique" => "Equipment with ID {value} doesn't exist."
            ]
        ];

        $this->validationRules['slot_id'] = [
            "rules" => [
                "permit_empty",
                "equipment_type_slot_id_is_sanitized",
                "slot_is_available[{parent_id},{child_id},id]",
            ]
        ];

        $this->validationRules['slot_name'] = [
            "rules" => [
                'required_without[slot_id]',
                'permit_empty',
                'equipment_type_slot_name_is_sanitized',
                "slot_is_available[{parent_id},{child_id},name]",
            ],
            'errors' => [
                'required_without' => 'A slot is required for the assembly.'
            ]
        ];
    }

    protected function getData()
    {
        $fieldValues = $this->getFieldValues();
        if (empty($fieldValues['slot_id'])){
            $fieldValues['slot_id'] = model('TypeSlotModel')
                ->where('name', $fieldValues['slot_name'])
                ->first()->id;
        }
        return $fieldValues;
    }
}
