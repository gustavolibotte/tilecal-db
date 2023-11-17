<?php

namespace App\Validation\Validators;
use App\Entities\EquipmentType;
use App\Validation\AtlasIdRules;
use CodeIgniter\Database\Exceptions\DataException;
use App\Entities\Equipment;
use Error;

class EquipmentValidator extends EntryValidator
{
    public EquipmentType $equipmentType;
    public static int $serialMaxSize = 36;
    public static int $maxCommentsSize = 65535; //MySQL max text size

    public array $fieldRename = [
        'detector_location' => 'detector_location_name',
        'owner_institute' => 'owner_mne',
        'location' => 'location_name',
        'status' => 'status_name',
        'owner' => 'owner_mne'
    ];

    public function __construct(EquipmentType|string $equipmentType)
    {
        if ($equipmentType instanceof EquipmentType){
            $this->equipmentType = $equipmentType;
        } else{
            $equipmentTypeEntity = model('EquipmentTypeModel')
                ->where('name', $equipmentType)->first();
            if (is_null($equipmentType)){
                throw new DataException(
                    sprintf("Could not find Equipment Type with name %s", $equipmentType)
                );
            }
            $this->equipmentType = $equipmentTypeEntity;
        }
        parent::__construct();
    }

    public function initialize()
    {
        parent::initialize();

        $this->validationRules['id'] = [
            "rules" => [
                "required",
                sprintf("exact_length[%s]", AtlasIdRules::$sequentialNumberLength),
                sprintf("regex_match[%s]", $this->equipmentType->id_regex),
                "is_unique[equipments.id]"
            ],
            "errors" => [
                "required" => "You must supply an id for the equipment.",
                "exact_length" => "The ID sequential number must have {param} characters. Value given: {value}",
                "regex_match" => "The ID doesn't follow this equipment ID rules. Value given: {value}",
                "is_unique" => "ID {value} already exists."
            ]
        ];

        $this->validationRules['owner_id'] = [
            "rules" => [
                "permit_empty",
                "is_not_unique[institutes.id]"
            ],
            "errors" => [
                "is_not_unique" => "The inserted owner is not valid. Value given {value}",
            ]
        ];

        $this->validationRules['owner_mne'] = [
            "rules" => [
                "required_without[owner_id]",
                "permit_empty",
                "is_not_unique[institutes.mne]"
            ],
            "errors" => [
                "required_without" => "You must supply an owner for the equipment.",
                "is_not_unique" => $this->validationRules["owner_id"]["errors"]["is_not_unique"]
            ]
        ];

        $this->validationRules['detector_location_id'] = [
            "rules" => [
                "permit_empty",
                "is_not_unique[detector_locations.id]"
            ],
            "errors" => [
                "is_not_unique" => "The inserted detector location is not valid."
            ]
        ];

        $this->validationRules['detector_location_name'] = [
            "rules" => [
                "permit_empty",
                "is_not_unique[detector_locations.name]"
            ],
            "errors" => [
                "is_not_unique" => "The inserted detector location is not valid."
            ]
        ];

        $this->validationRules['location_id'] = [
            "rules" => [
                "permit_empty",
                "is_not_unique[locations.id]"
            ],
            "errors" => [
                "is_not_unique" => "The inserted location is not valid.",
            ]
        ];

        $this->validationRules['location_name'] = [
            "rules" => [
                "required_without[location_id]",
                "permit_empty",
                "is_not_unique[locations.name]"
            ],
            "errors" => [
                "required_without" => "You must supply a location for the equipment.",
                "is_not_unique" =>  $this->validationRules["location_id"]["errors"]["is_not_unique"]
            ]
        ];

        $this->validationRules['status_id'] = [
            "rules" => [
                "permit_empty",
                "is_not_unique[statuses.id]"
            ],
            "errors" => [
                "is_not_unique" => "The inserted status is not valid."
            ]
        ];

        $this->validationRules['status_name'] = [
            "rules" => [
                "required_without[status_id]",
                "permit_empty",
                "is_not_unique[statuses.name]"
            ],
            "errors" => [
                "required_without" => "You must supply a status for the equipment.",
                "is_not_unique" =>  $this->validationRules["status_id"]["errors"]["is_not_unique"]
            ]
        ];

        $this->validationRules['serial'] = [
            "rules" => [
                "permit_empty",
                sprintf("max_length[%d]", self::$serialMaxSize),
                sprintf("is_type_unique[serial,%d]", $this->equipmentType->id)
            ],
            "errors" => [
                "max_length" => "Serial max size is {param} characters.",
                "is_type_unique" => "Serial Number {value} already exists."
            ]
        ];

        $this->validationRules['comments'] = [
            "rules" => [
                sprintf("max_length[%s]", Self::$maxCommentsSize)
            ],
            "errors" => [
                "max_length" => "Comments max size is {param} characthers.",
            ]
        ];
    }

    protected function getData(): Equipment
    {
        helper(['array', 'cast']);
        $fieldValues = $this->getFieldValues();
        $typeParams = [];
        foreach ($this->equipmentType->params as $param){
            $typeParams[$param] = remove_value($param, $fieldValues, false);
        }
        $equipment = new Equipment($fieldValues);
        $equipment->type_id = $this->equipmentType->id;
        $equipment->type_name = $this->equipmentType->name;
        $equipment->type_params = $typeParams;
        return $equipment;
    }

    public static function getFromTypeName(string $typeName): Self
    {
        helper('string');

        $className = sprintf('\App\Validation\Validators\%sValidator', snake2CamelCase($typeName));
        try {
            return new $className($typeName);
        } catch (Error $e){
            $expectedMsg = sprintf(
                'Class "%s" not found',
                $className
            );
            if($e->getMessage() === $expectedMsg){
                $form = new Self($typeName);
                return $form;
            }
            throw $e;
        } 
    }

    public function setFieldValues(array $data): Self
    {
        if (!array_key_exists('id', $data)){
            return parent::setFieldValues($data);
        }
        if (strlen($data['id']) != 14){
            return parent::setFieldValues($data);
        }
        $data['id'] = substr($data['id'], 7, 7);
        return parent::setFieldValues($data);
    }

    public function asEdit(): self
    {
        $this->removeRule('id', 'is_unique');
        $this->addRule('id', 'is_not_unique[equipments.id]', 'This id doesn\'t exist.');
        $oldRule = $this->removeRule('serial', 'is_type_unique');
        $newRule = str_replace('is_type_unique', 'is_type_not_unique', $oldRule);
        $this->addRule('serial', $newRule, 'The serial {value} doesn\'t exist');
        return $this;
    }
}
