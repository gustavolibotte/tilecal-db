<?php

namespace App\Library\Forms;
use App\Validation\AppFileRules;

class EquipmentBatchRegisterForm extends Form{

    public function initialize(){
        parent::initialize();

        $this->fields['type_name'] = [
            'id' => 'type_name',
            'label' => 'Equipment Type',
            'required' => true,
            'fieldType' => 'dropdown',
            'helpText' => "Equipment type in the file",
            'fieldParams' => [
                'options' => model('EquipmentTypeModel')
                    ->orderBy('label')
                    ->getCustomArray('name', 'label')
            ]
        ];

        $this->validationRules['type_name'] = [
            "rules" => [
                "required",
                "is_not_unique[equipment_types.name]",
            ],
            "errors" => [
                "required" => "You must supply an equipment type for registering.",
                "is_not_unique" => "The selected equipment type is not valid.",
            ]
        ];

        $this->fields['file'] = [
            'id' => 'file',
            'required' => true,
            'helpText' => sprintf("Max size: %d MB", AppFileRules::$maxFileSize),
            'fieldType' => 'upload',
            'fieldParams' => [
                'placeholder' => 'Select a file for upload'
            ]
        ];

        $this->validationRules['file'] = [
            "rules" => [
                "uploaded[file]", 
                sprintf("max_size[file,%d]", 1),
                "ext_in[file,csv]"
            ],
            "errors" => [
                "uploaded" => "You must upload a file.",
                "max_size" => sprintf('Max file size is %d MB', AppFileRules::$maxFileSize),
                "ext_in" => "Only csv files are supported for batch registering."
            ]
        ];
    }

        protected function getData()
        {
            return $this->getFieldValues();
        }
}