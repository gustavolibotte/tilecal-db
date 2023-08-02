<?php

namespace App\Library\Forms;

class EquipmentForm extends Form{

    public $validationRules;
    public $fields;

    public function initialize()
    {
        $this->fields = [
            'barcode' => [
                'id' => 'barcode',
                'required' => true,
                'helpText' => "Equipment's barcode value",
                'fieldType' => 'input',
                'fieldParams' => [
                    'maxlength' => 7,
                    'placeholder' => 'XXXXXXX'
                ]
            ],
            'model_label' => [
                'id' => 'model_label',
                'label' => 'Model',
                'required' => true,
                'fieldType' => 'input',
                'fieldParams' => [
                    'readOnly' => null
                ]
            ],
            'location' => [
                'id' => 'location',
                'required' => true,
                'fieldType' => 'dropdown',
                'fieldParams' => [
                    'options' => (new \App\Models\LocationModel())->getFormDropdownOptions()
                ]
            ],
            'status' => [
                'id' => 'status',
                'required' => true,
                'fieldType' => 'dropdown',
                'fieldParams' => [
                    'options' => (new \App\Models\StatusModel())->getFormDropdownOptions()
                ]
            ],
            'serial' => [
                'id' => 'serial',
                'required' => false,
                'fieldType' => 'input',
                'fieldParams' => [
                    'maxlength' => 20
                ]
            ],
            'comments' => [
                'id' => 'comments',
                'required' => false,
                'fieldType' => 'textarea',
                'fieldParams' => [
                    'rows' => 3
                ]
            ],
        ];

        $this->validationRules = [
            "barcode" => [
                "rules" => [
                    "required",
                    "alpha_numeric",
                    "exact_length[7]",
                    "is_unique[equipments.barcode]"
                ],
                "errors" => [
                    "required" => "You must supply a barcode for the equipment.",
                    "alpha_numeric" => "The barcode must be alphanumeric.",
                    "exact_length" => "The barcode must have 7 characthers.",
                    "is_unique" => "This barcode already exists."
                ]
            ],
            "model_label" => [
                "rules" => [
                    "required",
                    "is_not_unique[models.name]",
                ],
                "errors" => [
                    "required" => "You must supply a model for the equipment.",
                    "is_not_unique" => "The given model doesn't exist.",
                ]
            ],
            "location" => [
                "rules" => [
                    "required",
                    "is_not_unique[locations.name]",
                ],
                "errors" => [
                    "required" => "You must supply a location for the equipment.",
                    "is_not_unique" => "The inserted location is not valid",
                ]
            ],
            "status" => [
                "rules" => [
                    "required",
                    "is_not_unique[statuses.name]",
                ],
                "errors" => [
                    "required" => "You must supply a status for the equipment.",
                    "is_not_unique" => "The inserted status is not valid",
                ]
            ],
            "serial" => [
                "rules" => [
                    "max_length[20]"
                ],
                "errors" => [
                    "max_length" => "Serial max size is 20 characthers",
                ]
            ],
            "comments" => [
                "rules" => [
                    "max_length[65535]" //MySQL max text size
                ],
                "errors" => [
                    "max_length" => "Comments max size is 65535 characthers",
                ]
            ],
        ];
    }
}
