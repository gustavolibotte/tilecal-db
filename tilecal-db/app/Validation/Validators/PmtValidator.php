<?php

namespace App\Validation\Validators;

class PmtValidator extends EquipmentValidator
{
    public static array $validPmtModelValues = [
        'R11187',
        'R7877',
        'R7787',
        'R11187-SEL',
        'R11187-A1'
    ];
    public static string $validSerialRegex = '/^[A-Z0-9]{4}/';
    public static int $serialMaxSize = 6;

    public static int $smallestPmtPosition = 0;
    public static int $biggestPmtPosition = 48;

    public function initialize()
    {
        parent::initialize();
        helper('rule_manager');

        // Id is automatically generated
        $this->validationRules['id'] = [
            "rules" => [],
            "errors" => []
        ];

        $this->validationRules['serial'] = [
            "rules" => [
                "required",
                sprintf("exact_length[%d]", self::$serialMaxSize),
                sprintf('regex_match[%s]', self::$validSerialRegex),
                sprintf("is_type_unique[serial,%d]", $this->equipmentType->id)
            ],
            "errors" => [
                'required' => "You must supply a PMT serial for the equipment.",
                "exact_length" => "Serial size must be {param} characters.",
                'regex_match' => 'The serial code must have the first 2 characters as upper case letters and the last 4 as digits.',
                "is_type_unique" => "Serial Number {value} already exists."
            ]
        ];

        $this->updateRules['serial'] = [
            "rules" => [
                "permit_empty",
                sprintf("exact_length[%d]", self::$serialMaxSize),
                sprintf('regex_match[%s]', self::$validSerialRegex),
                sprintf("is_type_not_unique[serial,%d]", $this->equipmentType->id)
            ],
            "errors" => [
                "exact_length" => "Serial size must be {param} characters.",
                'regex_match' => 'The serial code must have the first 2 characters as upper case letters and the last 4 as digits.',
                "is_type_not_unique" => "Serial Number {value} doesn\'t exist."
            ]
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

        $this->updateRules['beta'] = $this->validationRules['beta'];

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

        $this->updateRules['nominal_hv'] = $this->validationRules['nominal_hv'];

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

        $this->updateRules['qe'] = $this->validationRules['qe'];

        $this->validationRules['model'] = [
            "rules" => [
                "required",
                sprintf("in_list[%s]", implode(', ', self::$validPmtModelValues)),
            ],
            "errors" => [
                "required" => "You must supply a PMT model for the equipment.",
                "in_list" => "The PMT model value is not valid. Choose one of {param}",
            ]
        ];

        $this->updateRules['model'] = remove_rule(
            $this->validationRules['model'], 'required'
        );
        $this->updateRules['model'] = add_rule(
            $this->updateRules['model'], 'permit_empty', 'start'
        );

        $this->validationRules['phase1_position'] = [
            "rules" => [
                "permit_empty",
                sprintf("greater_than_equal_to[%d]", self::$smallestPmtPosition),
                sprintf("less_than_equal_to[%d]", self::$biggestPmtPosition),
            ],
            "errors" => [
                "greater_than_equal_to" => "Min value for position is {param}, got {value}.",
                "less_than_equal_to" => "Max value for position is {param}, got {value}",
            ]
        ];

        $this->updateRules['phase1_position'] = $this->validationRules['phase1_position'];

    }

    public function setFieldValues($data): Self
    {
        if (array_key_exists('serial', $data)){
            $data['id'] = sprintf('0%s', $data['serial']);
        }
        return parent::setFieldValues($data);
    }
}
