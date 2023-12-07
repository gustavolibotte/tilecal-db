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
                sprintf("exact_length[%s]", AtlasIdRules::$sequentialNumberLength),
                "is_not_unique[equipments.id]"
            ],
            "errors" => [
                "required" => "You must supply an id for the main equipment.",
                "exact_length" => "The ID sequential number must have {param} characters. Value given: {value}",
                "is_not_unique" => "ID {value} doesn't exist."
            ]
        ];

        $this->validationRules['child_id'] = [
            "rules" => [
                "required",
                sprintf("exact_length[%s]", AtlasIdRules::$sequentialNumberLength),
                "is_not_unique[equipments.id]",
                "equip_is_not_assembled_into"
            ],
            "errors" => [
                "required" => "You must supply an id for the external equipment.",
                "exact_length" => "The ID sequential number must have {param} characters. Value given: {value}",
                "is_not_unique" => "ID {value} doesn't exist."
            ]
        ];

        $this->validationRules['slot_id'] = [
            "rules" => [
                "required",
                "slot_is_available[{parent_id},{child_id}]",
            ],
            "errors" => [
                "required" => "You must supply a slot."
            ]
        ];
    }
}
