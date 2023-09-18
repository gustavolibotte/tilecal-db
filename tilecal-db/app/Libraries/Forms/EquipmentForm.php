<?php

namespace App\Library\Forms;

use \App\Models\BaseModel;
use \App\Entities\Equipment;
use \App\Entities\AtlasId;
use App\Entities\EquipmentType;
use \App\Validation\AtlasIdRules;
use \App\Validation\EquipmentRules;
use \CodeIgniter\HTTP\IncomingRequest;
use Error;

class EquipmentForm extends Form{

    public array $validParents = [];
    public string $typeName;
    public EquipmentType $equipmentType;

    public function __construct(?IncomingRequest $request=null, string $typeName)
    {
        $this->equipmentType = model('EquipmentTypeModel')
            ->where('name', $typeName)
            ->first();
        parent::__construct($request);
        $this->setFieldValues(['type_name' => $typeName]);
    }

    public function initialize()
    {
        parent::initialize();

        $this->fields['id'] = [
            'id' => 'id',
            'required' => true,
            'helpText' => "Equipment's barcode value following ATLAS identification rules.",
            'fieldType' => 'input',
            'fieldParams' => [
                'maxlength' => 14,
                'placeholder' => 'XXXXXXXXXXXXXX'
            ]
        ];

        $this->validationRules['id'] = [
            "rules" => [
                "required",
                sprintf("exact_length[%s]", AtlasIdRules::$exactLength),
                sprintf("id_is_system[%s]", $this->equipmentType->system),
                "id_is_project[20]",
                "id_has_valid_institute[institutes.mne]",
                "is_unique[equipments.id]"
            ],
            "errors" => [
                "required" => "You must supply an id for the equipment.",
                "exact_length" => "The id must have {param} characters.",
                "id_is_system" => sprintf(
                    "For %s the ID system must be {param} for %s",
                    $this->equipmentType->label,
                    AtlasIdRules::$validSystems[$this->equipmentType->system]
                ),
                "id_is_project" => "The ID project for ATLAS must be {param}",
                "is_unique" => "This id already exists.",
            ]
        ];

        $this->fields['location_id'] = [
            'id' => 'location_id',
            'label' => 'Location',
            'required' => true,
            'fieldType' => 'dropdown',
            'helpText' => "Current equipment location",
            'fieldParams' => [
                'options' => (new \App\Models\LocationModel())->getFormDropdownOptions(['name', 'description'])
            ]
        ];
        
        $this->validationRules['location_id'] = [
            "rules" => [
                "required",
                "is_not_unique[locations.id]",
            ],
            "errors" => [
                "required" => "You must supply a location for the equipment.",
                "is_not_unique" => "The inserted location is not valid",
            ]
        ];

        $this->fields['status_id'] = [
            'id' => 'status_id',
            'label' => 'Status',
            'required' => true,
            'fieldType' => 'dropdown',
            'helpText' => "Current equipment status",
            'fieldParams' => [
                'options' => (new \App\Models\StatusModel())->getFormDropdownOptions(['name', 'description'])
            ]
        ];

        $this->validationRules['status_id'] = [
            "rules" => [
                "required",
                "is_not_unique[statuses.id]",
            ],
            "errors" => [
                "required" => "You must supply a status for the equipment.",
                "is_not_unique" => "The inserted status is not valid",
            ]
        ];

        $typeSlotModel = model('TypeSlotModel');
        if($typeSlotModel->hasSlots($this->equipmentType->name)){
            $this->fields['parent_id'] = [
                'id' => 'parent_id',
                'label' => 'Parent ID',
                'required' => false,
                'helpText' => "Parent Equipment's barcode value following ATLAS identification rules.",
                'fieldType' => 'input',
                'fieldParams' => [
                    'maxlength' => 14,
                    'placeholder' => 'XXXXXXXXXXXXXX'
                ]
            ];

            $this->validationRules['parent_id'] = [
                "rules" => [
                    "permit_empty",
                    sprintf("exact_length[%s]", AtlasIdRules::$exactLength),
                    'id_has_valid_project',
                    "id_has_valid_system",
                    "id_has_valid_institute[institutes.mne]",
                    "is_is_valid_parent[{id}]"
                ],
                "errors" => [
                    "required" => "You must supply an id for the equipment.",
                    "exact_length" => "The id must have {param} characters.",
                ]
            ];
        }

        $this->fields['serial'] = [
            'id' => 'serial',
            'required' => false,
            'fieldType' => 'input',
            'helpText' => "Equipment serial number",
            'fieldParams' => [
                'maxlength' => EquipmentRules::$serialMaxSize,
                'placeholder' => str_repeat('X', EquipmentRules::$serialMaxSize)
            ]
        ];

        $this->validationRules['serial'] = [
            "rules" => [
                "permit_empty",
                sprintf("max_length[%d]", EquipmentRules::$serialMaxSize),
                sprintf("is_type_unique[serial,%d]", $this->equipmentType->id)
            ],
            "errors" => [
                "max_length" => "Serial max size is {param} characters.",
                "is_type_unique" => "Serial Number {value} already exists."
            ]
        ];

        $this->fields['comments'] = [
            'id' => 'comments',
            'required' => false,
            'fieldType' => 'textarea',
            'helpText' => "Possible necessary comments on the equipment situtation.",
            'fieldParams' => [
                'rows' => 3
            ]
        ];

        $this->validationRules['comments'] = [
            "rules" => [
                sprintf("max_length[%s]", EquipmentRules::$maxCommentsSize)
            ],
            "errors" => [
                "max_length" => "Comments max size is {param} characthers",
            ]
        ];
    }

    public function getEntity(): Equipment
    {
        helper(['array', 'cast']);
        if (!$this->isValid){
            throw new \Exception('To get the equipment you must validate the form first');
        }
        $fieldValues = $this->getFieldValues();
        foreach ($fieldValues as $key=>&$value){
            $value = null_empty($value);
        }
        $equipment = new Equipment($fieldValues);
        $id = new AtlasId(['id' => $equipment->id]);
        $owner = (new \App\Models\InstituteModel())->where(sprintf("mne = '%s'", $id->institute))->first();
        $equipment->owner_id = $owner->id;
        $equipment->type_id = $this->equipmentType->id;
        $typeParams = [];
        foreach ($this->equipmentType->params as $param){
            $typeParams[$param] = $fieldValues[$param];
        }
        if ($equipment->parent_id == ''){
            $equipment->parent_id = null;
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

        if ($this->fieldExists('id')){
            $this->removeRule('id', 'is_unique');
            $this->addRule('id', 'is_not_unique[equipments.id]', 'This id doesn\'t exist.');
            $this->setReadOnly('id');
        }

        $oldRule = $this->removeRule('serial', 'is_type_unique');
        $newRule = str_replace('is_type_unique', 'is_type_not_unique', $oldRule);
        $this->addRule('serial', $newRule, 'The serial {value} doesn\'t exist');
        $this->setReadOnly('serial');

        $this->resetRules();
    }

    public static function getTypeForm(string $typeName, ?IncomingRequest $request=null): EquipmentForm
    {
        helper('string');
        $formClass = sprintf('\App\Library\Forms\%sForm', snake2CamelCase($typeName));
        try {
            return new $formClass($request, $typeName);
        } catch (Error $e){
            $expectedMsg = sprintf(
                'Class "%s" not found',
                $formClass
            );
            if($e->getMessage() == $expectedMsg){
                $form = new Self($request, $typeName);
                return $form;
            }
            throw $e;
        } 
    }
}
