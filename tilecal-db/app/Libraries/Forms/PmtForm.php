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
        $this->fields['id']['required'] = false;
        $this->fields['id']['helpText'] .= ' For PMTs the id is automatically built if left blank';
        $this->validationRules['id']['rules'] = [
            "required",
            "alpha_numeric",
            "exact_length[14]",
            "string_starts_with[20LOCER0]",
            "string_ends_with[{serial}]",
            "is_unique[equipments.id]"
        ];
        $this->validationRules['id']['errors'] = [
            "required" => "You must supply an id for the equipment.",
            "alpha_numeric" => "The id must be alphanumeric.",
            "exact_length" => "The id must have 14 characters.",
            "is_unique" => "This id already exists.",
            "string_starts_with" => "PMT ID must start with 20LOCER0. (20 for ATLAS peoject, LO for optics equipment and CER for CERN).",
            "string_ends_with" => "The final 6 characters of a PMT barcode must be its serial code."
        ];

        $this->validationRules['parent_id']['rules'][] = "equipment_is_type[pmt_block]";
        $this->validationRules['parent_id']['rules'][] = "not_maxed_parent[1,pmt,{id}]";

        $this->fields['serial']['required'] = true;
        $this->fields['serial']['helpText'] .= sprintf(' (Max size of %d characters)', self::$serialMaxSize);
        $this->fields['serial']['fieldParams']['maxlength'] = self::$serialMaxSize;
        $this->validationRules['serial']['rules'] = [
            "required",
            sprintf("max_length[%d]", self::$serialMaxSize),
            sprintf('regex_match[%s]', self::$validSerialRegex),
            sprintf("is_type_unique[serial,%d]", self::$pmtId)
        ];
        $this->validationRules['serial']['errors'] = [
            "required" => "A serial code is required",
            "max_length" => "Serial max size is {param} characters",
            'regex_match' => 'The serial code must have the first 2 characters as upper case letters and the last 4 as digits.',
            "is_type_unique" => "There is already a PMT registered with the serial {value}"
        ];

        $this->fields['beta'] = [
            'id' => 'beta',
            'required' => true,
            'fieldType' => 'input',
            'helpText' => 'Beta must be a postive number',
            'fieldParams' => []
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
            'helpText' => 'Nominal HV must be a postive number',
            'fieldParams' => []
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
            'helpText' => 'QE must be a postive number',
            'fieldParams' => []
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
        $values = $this->getFieldValues();
        $values['type_label'] = 'PMT';
        $this->setFieldValues($values);
    }

    protected function processRequest()
    {
        parent::processRequest();
        $values = $this->getFieldValues();
        $newValues['id'] = "20LOCER0{$values['serial']}";
        $this->setFieldValues($newValues);
    }

    public function asEdit(){

        $this->fields['id']['fieldParams']['readOnly'] = null;
        $this->validationRules['id']['rules'] = [
            "required",
            "alpha_numeric",
            "exact_length[14]",
            "string_starts_with[20LOCER0]",
            "string_ends_with[{serial}]",
            "is_not_unique[equipments.id]"
        ];
        $this->validationRules['id']['errors'] = [
            "alpha_numeric" => "The id must be alphanumeric.",
            "exact_length" => "The id must have 14 characters.",
            "is_not_unique" => "This id doesn't exists.",
            "string_starts_with" => "PMT ID must start with 20LOCER0. (20 for ATLAS peoject, LO for optics equipment and CER for CERN).",
            "string_ends_with" => "The final 6 characters of a PMT barcode must be its serial code."
        ];

        $this->fields['serial']['fieldParams']['readOnly'] = null;
        $this->validationRules['serial']['rules'] = [
            "required",
            sprintf("max_length[%d]", self::$serialMaxSize),
            sprintf('regex_match[%s]', self::$validSerialRegex),
            sprintf("is_type_not_unique[serial,%d]", self::$pmtId)
        ];
        $this->validationRules['serial']['errors'] = [
            "required" => "A serial code is required",
            "max_length" => "Serial max size is {param} characters",
            'regex_match' => 'The serial code must have the first 2 characters as upper case letters and the last 4 as digits.',
            "is_type_not_unique" => "There is no PMT with the serial {value}"
        ];

        $this->resetRules();
    }
}
