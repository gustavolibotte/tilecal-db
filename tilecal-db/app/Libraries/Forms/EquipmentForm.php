<?php

namespace App\Library\Forms;

use App\Entities\EquipmentType;
use App\Validation\AtlasIdRules;
use \App\Validation\EquipmentRules;
use \CodeIgniter\HTTP\IncomingRequest;
use Error;

class EquipmentForm extends Form{

    public array $validParents = [];
    public string $typeName;
    public EquipmentType $equipmentType;

    public function __construct(string $typeName, ?IncomingRequest $request=null)
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

        $this->fields['owner_id'] = [
            'id' => 'owner_id',
            'label' => 'Owner Institute',
            'required' => true,
            'fieldType' => 'dropdown',
            'helpText' => "Institute that owns the equipment",
            'fieldParams' => [
                'options' => model('InstituteModel')
                    ->orderBy('name')
                    ->getFormDropdownOptions(['mne', 'name'])
            ]
        ];

        $this->fields['location_id'] = [
            'id' => 'location_id',
            'label' => 'Location',
            'required' => true,
            'fieldType' => 'dropdown',
            'helpText' => "Current equipment location",
            'fieldParams' => [
                'options' => model('LocationModel')
                    ->orderBy('name')
                    ->getFormDropdownOptions(['name', 'description'])
            ]
        ];

        $this->fields['detector_location_id'] = [
            'id' => 'detector_location_id',
            'label' => 'Detector Location',
            'required' => false,
            'fieldType' => 'dropdown',
            'helpText' => 'Where in the detector this equipment should be.',
            'fieldParams' => [
                'options' => model('DetectorLocationModel')
                    ->orderBy('name')
                    ->getFormDropdownOptions(['name'])
            ]
        ];

        $this->fields['status_id'] = [
            'id' => 'status_id',
            'label' => 'Status',
            'required' => true,
            'fieldType' => 'dropdown',
            'helpText' => "Current equipment status",
            'fieldParams' => [
                'options' => model('StatusModel')
                    ->orderBy('name')
                    ->getFormDropdownOptions(['name', 'description'])
            ]
        ];

        $this->fields['serial'] = [
            'id' => 'serial',
            'required' => false,
            'fieldType' => 'input',
            'helpText' => "Equipment serial number.",
            'fieldParams' => [
                'maxlength' => EquipmentRules::$serialMaxSize,
                'placeholder' => str_repeat('X', EquipmentRules::$serialMaxSize)
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
    }

    protected function commentsEnd(){
        $commentsField = $this->fields['comments'];
        unset($this->fields['comments']);
        $this->fields['comments'] = $commentsField;
    }

    public function asEdit(){

        if ($this->fieldExists('id')){
            $this->setReadOnly('id');
        }
        $this->setReadOnly('serial');
    }

    public static function getTypeForm(string $typeName, ?IncomingRequest $request=null): EquipmentForm
    {
        helper('string');
        $formClass = sprintf('\App\Library\Forms\%sForm', snake2CamelCase($typeName));
        try {
            return new $formClass($typeName, $request);
        } catch (Error $e){
            $expectedMsg = sprintf(
                'Class "%s" not found',
                $formClass
            );
            if($e->getMessage() == $expectedMsg){
                $form = new Self($typeName, $request);
                return $form;
            }
            throw $e;
        } 
    }
}
