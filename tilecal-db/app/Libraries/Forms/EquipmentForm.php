<?php

namespace App\Library\Forms;

use \App\Models\BaseModel;
use \App\Entities\Equipment;

class EquipmentForm extends Form{

    public static int $serialMaxSize = 36;

    public function initialize()
    {
        parent::initialize();
        $this->fields = [
            'id' => [
                'id' => 'id',
                'required' => true,
                'helpText' => "Equipment's barcode value following ATLAS identification rules.",
                'fieldType' => 'input',
                'fieldParams' => [
                    'maxlength' => 14,
                    'placeholder' => 'XXXXXXXXXXXXXX'
                ]
            ],
            'type_label' => [
                'id' => 'type_label',
                'label' => 'Model',
                'required' => true,
                'helpText' => "Equipment's type",
                'fieldType' => 'hidden',
                'fieldParams' => []
            ],
            'location_id' => [
                'id' => 'location_id',
                'label' => 'Location',
                'required' => true,
                'fieldType' => 'dropdown',
                'helpText' => "Current equipment location",
                'fieldParams' => [
                    'options' => (new \App\Models\LocationModel())->getFormDropdownOptions(['name', 'description'])
                ]
            ],
            'status_id' => [
                'id' => 'status_id',
                'label' => 'Status',
                'required' => true,
                'fieldType' => 'dropdown',
                'helpText' => "Current equipment status",
                'fieldParams' => [
                    'options' => (new \App\Models\StatusModel())->getFormDropdownOptions(['name', 'description'])
                ]
            ],
            'serial' => [
                'id' => 'serial',
                'required' => false,
                'fieldType' => 'input',
                'helpText' => "Equipment serial number",
                'fieldParams' => [
                    'maxlength' => self::$serialMaxSize
                ]
            ],
            'comments' => [
                'id' => 'comments',
                'required' => false,
                'fieldType' => 'textarea',
                'helpText' => "Possible necessary comments on the equipment situtation.",
                'fieldParams' => [
                    'rows' => 3
                ]
            ],
        ];

        $this->validationRules = [
            "id" => [
                "rules" => [
                    "required",
                    "alpha_numeric",
                    "exact_length[14]",
                    "id_is_project[20]",
                    "id_has_valid_institute[institutes.mne]",
                    "is_unique[equipments.id]"
                ],
                "errors" => [
                    "required" => "You must supply an id for the equipment.",
                    "alpha_numeric" => "The id must be alphanumeric.",
                    "exact_length" => "The id must have 14 characters.",
                    "is_unique" => "This id already exists.",
                    "id_is_project" => "An equipment barcode must start with 20 (20 is code for ATLAS detector)",
                    "id_has_valid_institute" => "The barcode doesn't have a valid institute value"
                ]
            ],
            "type_label" => [
                "rules" => [
                    "required",
                    "is_not_unique[equipment_types.label]",
                ],
                "errors" => [
                    "required" => "You must supply a type for the equipment.",
                    "is_not_unique" => "The given type doesn't exist.",
                ]
            ],
            "location_id" => [
                "rules" => [
                    "required",
                    "is_not_unique[locations.id]",
                ],
                "errors" => [
                    "required" => "You must supply a location for the equipment.",
                    "is_not_unique" => "The inserted location is not valid",
                ]
            ],
            "status_id" => [
                "rules" => [
                    "required",
                    "is_not_unique[statuses.id]",
                ],
                "errors" => [
                    "required" => "You must supply a status for the equipment.",
                    "is_not_unique" => "The inserted status is not valid",
                ]
            ],
            "serial" => [
                "rules" => [
                    sprintf("max_length[%d]", self::$serialMaxSize)
                ],
                "errors" => [
                    "max_length" => "Serial max size is {param} characters",
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

    public function getEntity(): Equipment
    {
        helper(['array', 'atlas_id']);
        if (!$this->isValid){
            throw new \Exception('To get the equipment you must validate the form first');
        }
        $fieldValues = $this->getFieldValues();
        $equipment = new Equipment($fieldValues);
        $instituteMne = getInstituteMne($equipment->id);
        $owner = (new \App\Models\InstituteModel())->where(sprintf("mne = '%s'", $instituteMne))->first();
        $equipment->owner_id = $owner->id;
        $equipmentType = (new \App\Models\EquipmentTypeModel())->where(sprintf("label = '%s'", $fieldValues['type_label']))->first();
        $equipment->type_id = $equipmentType->id;
        $typeParams = [];
        foreach ($equipmentType->params as $param){
            $typeParams[$param] = $this->request->getVar($param);
        }
        $equipment->type_params = $typeParams;
        return $equipment;
    }

    protected function getId(string $fieldName, string $value, BaseModel $model){
        $entity = $model->where(sprintf("%s = '%s'", $fieldName, $value))->first();
        return $entity->id;
    }

    protected function commentsEnd(){
        $commentsField = $this->fields['comments'];
        unset($this->fields['comments']);
        $this->fields['comments'] = $commentsField;
    }

    public function asEdit(){
        
        $this->validationRules['id'] = [
            "rules" => [
                "required",
                "alpha_numeric",
                "exact_length[14]",
                "id_is_project[20]",
                "id_has_valid_institute[institutes.mne]",
                "is_not_unique[equipments.id]"
            ],
            "errors" => [
                "required" => "You must supply an id for the equipment.",
                "alpha_numeric" => "The id must be alphanumeric.",
                "exact_length" => "The id must have 14 characters.",
                "is_not_unique" => "This id doesn't exist.",
                "id_is_project" => "An equipment barcode must start with 20 (20 is code for ATLAS detector)",
                "id_has_valid_institute" => "The barcode doesn't have a valid institute value"
            ]
        ];
        $this->resetRules();
        $this->fields['id']['fieldParams']['readOnly'] = null;
    }
}
