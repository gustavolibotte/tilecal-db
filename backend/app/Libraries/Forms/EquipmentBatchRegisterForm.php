<?php

namespace App\Library\Forms;
use App\Validation\Validators\BatchRegisterValidator;

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
                    ->orderBy('name')
                    ->getFormDropdownOptions(['name'], value:'name')
            ]
        ];

        $this->fields['file'] = [
            'id' => 'file',
            'required' => true,
            'helpText' => sprintf("Max size: %d kB", BatchRegisterValidator::$maxFileSize),
            'fieldType' => 'upload',
            'fieldParams' => [
                'placeholder' => 'Select a file for upload'
            ]
        ];
    }

        protected function getData()
        {
            return $this->getFieldValues();
        }
}