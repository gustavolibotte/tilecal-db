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
    public static int $pmtSerialMaxSize = 6;

    public static int $smallestPmtPosition = 0;
    public static int $biggestPmtPosition = 48;

    public function initialize()
    {
        parent::initialize();

        $this->addRule('id', 'string_ends_with[{serial}]',
            "The final 6 characters of a PMT barcode must be its serial code.");

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

        $this->validationRules['phase1_position'] = [
            "rules" => [
                "if_exist",
                "permit_empty",
                sprintf("greater_than_equal_to[%d]", self::$smallestPmtPosition),
                sprintf("less_than_equal_to[%d]", self::$biggestPmtPosition),
            ],
            "errors" => [
                "greater_than_equal_to" => "Min value for position is {param}.",
                "less_than_equal_to" => "Max value for position is {param}",
            ]
        ];
    }

    public function setFieldValues($data): Self
    {
        if (array_key_exists('serial', $data)){
            $data['id'] = sprintf('0%s', $data['serial']);
        }
        return parent::setFieldValues($data);
    }
}
