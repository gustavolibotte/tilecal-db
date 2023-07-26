<?php

namespace App\Library\FormUtils;

class Equipment{

    public $equipment = null;

    public function __construct($equipment=null)
    {
        if (is_null($equipment)){
            $this->equipment = new \App\Entities\Equipment();
        } else{
            $this->$equipment = $equipment;
        }
    }

    public function getFormFields($values){
        helper('array');
        $formFields = [
            $this->getIdField(dot_array_search('id', $values)),
            $this->getModelLabelField($values['modelLabel']),

        ];

        return $formFields;
    }

    protected function getIdField($value=null){
        return $this->getFieldInfo([
            'id' => 'id',
            'required' => true,
            'type' => 'input',
            'placeholder' => 'Equipment barcode',
            'value' => $value,
        ]);
    }

    protected function getModelLabelField($value){
        return $this->getFieldInfo([
            'id' => 'id',
            'required' => true,
            'type' => 'input',
            'placeholder' => 'Equipment barcode',
            'value' => $value,
        ]);
    }

    protected function getFieldInfo($field){
        helper('string');
        helper('array');
        $labelText = snake2Capitalized($field['id']);
        $fieldInfo = [
            'id' => $field['id'],
            'labelInfo' => [
                'text' => dot_array_search('required', $field)? $labelText.'*:' : $labelText.':'
            ],
            'inputInfo' => [
                'formType' => $field['type'],
                'helperParams' => [
                    'name' => $field['id'],
                    'id' => $field['id'],
                    'class' => 'form-control',
                    'placeholder' => dot_array_search('placeholder', $field),
                    'value' => dot_array_search('value', $field)
                ]
            ],
                'helpInfo' => [
                    'text' => dot_array_search('help', $field)
                ]
        ];
        return $fieldInfo;
    }
}
