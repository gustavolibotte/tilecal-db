<?php

namespace App\Validation\Validators;
use App\Entities\AtlasId;
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

        helper('rule_manager');

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

        $this->updateRules['id'] = [
            "rules" => [
                "required",
                "is_not_unique[equipments.id]"
            ],
            'errors' => [
                'required' => 'You must supply an id for updating the equipment data.',
                'is_not_unique' => 'ID {value} doesn\'t exist.'
            ]
        ];

        $this->validationRules['owner_id'] = [
            "rules" => [
                "permit_empty",
                "is_not_unique[institutes.id]"
            ],
            "errors" => [
                "is_not_unique" => "The given owner id ({value}) is not valid.",
            ]
        ];

        $this->updateRules['owner_id'] = $this->validationRules['owner_id'];

        $this->validationRules['owner_mne'] = [
            "rules" => [
                "required_without[owner_id]",
                "permit_empty",
                "is_not_unique[institutes.mne]"
            ],
            "errors" => [
                "required_without" => "You must supply an owner for the equipment.",
                "is_not_unique" => "The inserted owner MNE ({value}) is not valid."
            ]
        ];

        $this->updateRules['owner_mne'] = remove_rule(
            $this->validationRules['owner_mne'], 'required_without'
        );

        $this->validationRules['detector_location_id'] = [
            "rules" => [
                "permit_empty",
                "is_not_unique[detector_locations.id]"
            ],
            "errors" => [
                "is_not_unique" => "The given detector location id ({value}) is not valid."
            ]
        ];

        $this->updateRules['detector_location_id'] = $this->validationRules['detector_location_id'];

        $this->validationRules['detector_location_name'] = [
            "rules" => [
                "permit_empty",
                "is_not_unique[detector_locations.name]"
            ],
            "errors" => [
                "is_not_unique" => "The given detector location name ({value}) is not valid."
            ]
        ];

        $this->updateRules['detector_location_name'] = $this->validationRules['detector_location_name'];

        $this->validationRules['location_id'] = [
            "rules" => [
                "permit_empty",
                "is_not_unique[locations.id]"
            ],
            "errors" => [
                "is_not_unique" => "The inserted location id ({value}) is not valid.",
            ]
        ];

        $this->updateRules['location_id'] = $this->validationRules['location_id'];

        $this->validationRules['location_name'] = [
            "rules" => [
                "required_without[location_id]",
                "permit_empty",
                "is_not_unique[locations.name]"
            ],
            "errors" => [
                "required_without" => "You must supply a location for the equipment.",
                "is_not_unique" =>  "The inserted location name ({value}) is not valid.",
            ]
        ];

        $this->updateRules['location_name'] = remove_rule(
            $this->validationRules['location_name'], 'required_without'
        );

        $this->validationRules['status_id'] = [
            "rules" => [
                "permit_empty",
                "is_not_unique[statuses.id]"
            ],
            "errors" => [
                "is_not_unique" => "The inserted status id ({value}) is not valid."
            ]
        ];

        $this->updateRules['status_id'] = $this->validationRules['status_id'];

        $this->validationRules['status_name'] = [
            "rules" => [
                "required_without[status_id]",
                "permit_empty",
                "is_not_unique[statuses.name]"
            ],
            "errors" => [
                "required_without" => "You must supply a status for the equipment.",
                "is_not_unique" =>  "The inserted status name ({value}) is not valid."
            ]
        ];

        $this->updateRules['status_name'] = remove_rule(
            $this->validationRules['status_name'], 'required_without'
        );

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

        $this->updateRules['serial'] = $this->validationRules['serial'];

        $this->validationRules['comments'] = [
            "rules" => [
                "permit_empty",
                sprintf("max_length[%s]", Self::$maxCommentsSize)
            ],
            "errors" => [
                "max_length" => "Comments max size is {param} characters.",
            ]
        ];

        $this->updateRules['comments'] = $this->validationRules['comments'];
    }

    protected function getData()
    {
        $data = ($this->isEdit)? $this->getDataEdit(): $this->getDataInsert();
        return new Equipment($data);
    }

    protected function getDataEdit()
    {
        helper(['array', 'cast']);
        $fieldValues = $this->getFieldValues();
        $fieldValues['type_id'] = $this->equipmentType->id;
        $fieldValues['type_name'] = $this->equipmentType->name;
        $fieldValues = $this->substituteFKs(($fieldValues));

        $typeParams = model('EquipmentModel')
            ->select('type_params')
            ->find($fieldValues['id'])
            ->type_params;
        $hasTypeParams = false;
        foreach($this->equipmentType->params as $param){
            if (array_key_exists($param, $fieldValues)){
                $typeParams[$param] = cast_to_numeric(remove_value($param, $fieldValues));
                $hasTypeParams = true;
            }
        }
        if ($hasTypeParams){
            $fieldValues['type_params'] = $typeParams;
        }
        return $fieldValues;
    }

    protected function getDataInsert()
    {
        helper(['array', 'cast']);
        $fieldValues = $this->getFieldValues();
        $fieldValues = $this->substituteFKs($fieldValues);
        $typeParams = [];
        foreach ($this->equipmentType->params as $param){
            $typeParams[$param] = cast_to_numeric(remove_value($param, $fieldValues, false));
        }
        $fieldValues['type_id'] = $this->equipmentType->id;
        $fieldValues['type_name'] = $this->equipmentType->name;
        $fieldValues['type_params'] = $typeParams;
        return $fieldValues;
    }

    protected function hasTypeParamsField()
    {
        $fieldValues = $this->getFieldValues();
        $hasTypeParamsField = false;
        foreach($this->equipmentType->params as $param){
            if (array_key_exists($param, $fieldValues)){
                $hasTypeParamsField = true;
                break;
            }
        }
        return $hasTypeParamsField;
    }

    public static function getFromTypeName(string $typeName): Self
    {
        helper('string');

        $className = sprintf('\App\Validation\Validators\%sValidator', snake2CamelCase(makeSnakeCase($typeName)));
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
        $data['id'] = $this->processId($data['id']);

        return parent::setFieldValues($data);
    }

    public function processId(string $id): string
    {
        if (strlen($id) != 14){
            return $id;
        }
        $id = new AtlasId(['id' => $id]);
        return $id->getSequential;
    }

    public function substituteFKs(array $fieldValues)
    {
        $fieldValues = $this->substituteFK(
            'type_id',
            'type_name',
            'name',
            'EquipmentTypeModel',
            $fieldValues
        );
        $fieldValues = $this->substituteFK(
            'owner_id',
            'owner_mne',
            'mne',
            'InstituteModel',
            $fieldValues
        );
        $fieldValues = $this->substituteFK(
            'location_id',
            'location_name',
            'name',
            'LocationModel',
            $fieldValues
        );
        $fieldValues = $this->substituteFK(
            'status_id',
            'status_name',
            'name',
            'StatusModel',
            $fieldValues
        );
        $fieldValues = $this->substituteFK(
            'detector_location_id',
            'detector_location_name',
            'name',
            'DetectorLocationModel',
            $fieldValues
        );
        return $fieldValues;
    }
}
