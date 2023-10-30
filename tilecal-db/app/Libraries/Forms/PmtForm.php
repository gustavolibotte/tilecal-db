<?php

namespace App\Library\Forms;

class PmtForm extends EquipmentForm{

    public static array $pmtModelOptions = [
        'null' => 'Select an option',
        'R11187' => 'R11187',
        'R7877' => 'R7877',
        'R7787' => 'R7787',
        'R11187-SEL' => 'R11187-SEL',
        'R11187-A1' => 'R11187-A1'
    ];
    public static array $validPmtModelValues = [
        'R11187',
        'R7877',
        'R7787',
        'R11187-SEL',
        'R11187-A1'
    ];
    public static string $validSerialRegex = '/^[A-Z]{2}[0-9]{4}/';
    public static int $serialMaxSize = 6;
    public static int $pmtId = 1;
    public static int $pmtOwnerId = 103;
    public static int $maxNumberPerBlock = 1;

    public function initialize()
    {   
        parent::initialize();
        // Alter id and serial rules to follow custom PMT rules

        $this->fields['id']['fieldType'] = 'hidden';
        $this->removeRule('id', 'id_is_system');
        $this->removeRule('id', 'id_is_project');
        $this->removeRule('id', 'id_has_valid_institute');
        $this->addRule('id', 'string_ends_with[{serial}]',
            "The final 6 characters of a PMT barcode must be its serial code.");

        $this->fields['serial']['required'] = true;
        $this->fields['serial']['helpText'] .= ' For PMTs the id is automatically built from the serial number';
        $this->fields['serial']['helpText'] .= sprintf(' (Max size of %d characters)', self::$serialMaxSize);
        $this->fields['serial']['fieldParams']['maxlength'] = self::$serialMaxSize;
        $this->fields['serial']['fieldParams']['placeholder'] = str_repeat('X', self::$serialMaxSize);
        $this->addRule(
            'serial',
            sprintf('regex_match[%s]', self::$validSerialRegex),
            'The serial code must have the first 2 characters as upper case letters and the last 4 as digits.'
        );
        $this->addRule(
            'serial',
            'required',
            'PMT registration requires a serial code',
            'start'
        );

        $this->fields['beta'] = [
            'id' => 'beta',
            'required' => false,
            'fieldType' => 'input',
            'helpText' => 'Beta must be a postive number',
            'fieldParams' => []
        ];
        $this->validationRules['beta'] = [
            "rules" => [
                "permit_empty",
                "decimal",
                "greater_than[0]"
            ],
            "errors" => [
                "decimal" => "The Beta param must be a decimal number.",
                "greater_than" => "The Beta param must be greater than zero."
            ]
        ];

        $this->fields['nominal_hv'] = [
            'id' => 'nominal_hv',
            'label' => 'Nominal HV',
            'required' => false,
            'fieldType' => 'input',
            'helpText' => 'Nominal HV must be a postive number',
            'fieldParams' => []
        ];
        $this->validationRules['nominal_hv'] = [
            "rules" => [
                "permit_empty",
                "decimal",
                "greater_than[0]"
            ],
            "errors" => [
                "decimal" => "The Nominal HV must be a decimal number.",
                "greater_than" => "The Hominal HV must be greater than zero."
            ]
        ];

        $this->fields['qe'] = [
            'id' => 'qe',
            'label' => 'QE',
            'required' => false,
            'fieldType' => 'input',
            'helpText' => 'QE must be a postive number',
            'fieldParams' => []
        ];
        $this->validationRules['qe'] = [
            "rules" => [
                "permit_empty",
                "decimal",
                "greater_than[0]"
            ],
            "errors" => [
                "decimal" => "The QE must be a decimal number.",
                "greater_than" => "The QE must be greater than zero."
            ]
        ];

        $this->fields['model'] = [
            'id' => 'model',
            'required' => true,
            'fieldType' => 'dropdown',
            'fieldParams' => [
                'options' => self::$pmtModelOptions
            ]
        ];
        $validPMTModelsString = implode(', ', self::$validPmtModelValues);
        $this->validationRules['model'] = [
            "rules" => [
                "required",
                sprintf("in_list[%s]",$validPMTModelsString),
            ],
            "errors" => [
                "required" => "You must supply a PMT model for the equipment.",
                "in_list" => "The PMT model value is not valid. Choose one of {param}",
            ]
        ];
        $this->commentsEnd();
    }

    public function setFieldValues($data)
    {
        if (array_key_exists('serial', $data)){
            $data['id'] = sprintf('0%s', $data['serial']);
        }
        return parent::setFieldValues($data);
    }
}
