<?php

namespace App\Library\Forms;
use App\Validation\AppFileRules;

class EquipmentBatchRegisterForm extends Form{

    public function initialize(){
        parent::initialize();

        $this->fields['type_id'] = [
            'id' => 'type_id',
            'label' => 'Equipment Type',
            'required' => true,
            'fieldType' => 'dropdown',
            'helpText' => "Equipment type in the file",
            'fieldParams' => [
                'options' => model('EquipmentTypeModel')
                    ->orderBy('label')
                    ->getFormDropdownOptions(['label'])
            ]
        ];

        $this->validationRules['type_id'] = [
            "rules" => [
                "required",
                "is_not_unique[equipment_types.id]",
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
                sprintf("max_size[file,%d]", 1)
            ],
            "errors" => [
                "uploaded" => "You must upload a file.",
                "max_size" => sprintf('Max file size is %d MB', AppFileRules::$maxFileSize)
            ]
        ];
    }

        protected function getData()
        {
            return $this->getFieldValues();
        }
}