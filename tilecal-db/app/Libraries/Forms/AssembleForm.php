<?php

namespace App\Library\Forms;
use App\Validation\AtlasIdRules;
use CodeIgniter\HTTP\IncomingRequest;
use App\Entities\Equipment;

class AssembleForm extends Form{

    public Equipment $mainEquipment;

    function __construct(Equipment $mainEquipment, ?IncomingRequest $request=null){
        $this->mainEquipment = $mainEquipment;
        parent::__construct($request);
    }

    public function initialize(){
        parent::initialize();

        $this->fields['parent_id'] = [
            'id' => 'parent_id',
            'label' => 'Main Equipment ID',
            'required' => true,
            'fieldType' => 'hidden',
            'fieldParams' => []
        ];

        $this->fields['child_id'] = [
            'id' => 'child_id',
            'required' => true,
            'helpText' => 'Equipment ID to be assembled',
            'fieldType' => 'input',
            'fieldParams' => [
                'maxlength' => str_repeat('X', AtlasIdRules::$exactLength),
                'placeholder' => str_repeat('X', AtlasIdRules::$exactLength)
            ]
        ];

        $slotOptions = model('TypeSlotModel')
            ->where('type_slots.parent_type_id', $this->mainEquipment->type_id)
            ->orderBy('name')
            ->getFormDropdownOptions(['name']);
        helper('string');
        array_walk($slotOptions, function (&$value, $key){
            if ($key != FORM_SELECT_NULL_OPTION){
                $value = snake2Capitalized($value);
            }
        });
        $this->fields['slot_id'] = [
            'id' => 'slot_id',
            'label' => 'Slot',
            'required' => false,
            'fieldType' => 'dropdown',
            'helpText' => "Slot to fit the equipment",
            'fieldParams' => [
                'options' => $slotOptions
            ]
        ];

        $this->setFieldValues(['parent_id' => $this->mainEquipment->id]);
    }
}