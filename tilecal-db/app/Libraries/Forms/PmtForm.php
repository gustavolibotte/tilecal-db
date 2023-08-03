<?php

namespace App\Library\Forms;

class PmtForm extends EquipmentForm{

    public $formValues;
    public $validator;
    public $fields;
    public $validationRules;
    public $isValid = false;
    public $pmtTypeOptions = [
        'null' => 'Select an option',
        'R11187' => 'R11187',
        'R7877' => 'R7877'
    ];
    public $validPmtTypeValues = [
        'R11187',
        'R7877'
    ];

    public function initialize()
    {   
        parent::initialize();
        $this->formValues['model_label'] = 'PMT';

        $this->fields['beta'] = [
            'id' => 'beta',
            'required' => true,
            'fieldType' => 'input',
            'fieldParams' => [
                'placeholder' => 'Positive number'
            ]
        ];
        $this->validationRules['beta'] = [
            "rules" => [
                "required",
                "decimal",
                "greater_than[0]"
            ],
            "errors" => [
                "required" => "You must supply a Beta value for the equipment.",
                "decimal" => "The Beta param must be a decimal number.",
                "greater_than" => "The Beta param must be greater than zero."
            ]
        ];

        $this->fields['nominal_hv'] = [
            'id' => 'nominal_hv',
            'label' => 'Nominal HV',
            'required' => true,
            'fieldType' => 'input',
            'fieldParams' => [
                'placeholder' => 'Positive number'
            ]
        ];
        $this->validationRules['nominal_hv'] = [
            "rules" => [
                "required",
                "decimal",
                "greater_than[0]"
            ],
            "errors" => [
                "required" => "You must supply a Nominal HV value for the equipment.",
                "decimal" => "The Nominal HV must be a decimal number.",
                "greater_than" => "The Hominal HV must be greater than zero."
            ]
        ];

        $this->fields['qe'] = [
            'id' => 'qe',
            'label' => 'QE',
            'required' => true,
            'fieldType' => 'input',
            'fieldParams' => [
                'placeholder' => 'Positive number'
            ]
        ];
        $this->validationRules['qe'] = [
            "rules" => [
                "required",
                "decimal",
                "greater_than[0]"
            ],
            "errors" => [
                "required" => "You must supply a QE value for the equipment.",
                "decimal" => "The QE must be a decimal number.",
                "greater_than" => "The QE must be greater than zero."
            ]
        ];

        $this->fields['type'] = [
            'id' => 'type',
            'required' => true,
            'fieldType' => 'dropdown',
            'fieldParams' => [
                'options' => $this->pmtTypeOptions
            ]
        ];
        $validPMTTypesString = implode(', ', $this->validPmtTypeValues);
        $this->validationRules['type'] = [
            "rules" => [
                "required",
                sprintf("in_list[%s]",$validPMTTypesString),
            ],
            "errors" => [
                "required" => "You must supply a PMT model type for the equipment.",
                "in_list" => "The PMT type value is not valid",
            ]
        ];

        $this->commentsEnd();
    }

    public function clear(){
        parent::clear();
        $this->formValues['model_label'] = 'PMT';
    }
}
