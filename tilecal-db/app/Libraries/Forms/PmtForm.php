<?php

namespace App\Library\Forms;
use \App\Entities\Equipment;

class PmtForm extends EquipmentForm{
    public static array $pmtTypeOptions = [
        'null' => 'Select an option',
        'R11187' => 'R11187',
        'R7877' => 'R7877',
        'R7787' => 'R7787',
        'R11187-SEL' => 'R11187-SEL',
        'R11187-A1' => 'R11187-A1'
    ];
    public static array $validPmtTypeValues = [
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

        $this->fields['serial']['required'] = true;
        $this->fields['serial']['helpText'] .= sprintf(' (Max size of %d characters)', self::$serialMaxSize);
        $this->fields['serial']['maxlength'] = self::$serialMaxSize;
        $this->validationRules['serial']['rules'] = [
            "required",
            sprintf("max_length[%d]", self::$serialMaxSize),
            sprintf('regex_match[%s]', self::$validSerialRegex),
            sprintf("is_model_unique[serial,{%s}]", self::$pmtId)
        ];
        $this->validationRules['serial']['errors'] = [
            "required" => "A serial code is required",
            "max_length" => "Serial max size is {param} characters",
            'regex_match' => 'The serial code must have the first 2 characters as upper case letters and the last 4 as digits.',
            "is_model_unique" => "There is already a PMT registered with the serial {value}"
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

        $this->fields['type'] = [
            'id' => 'type',
            'required' => true,
            'fieldType' => 'dropdown',
            'fieldParams' => [
                'options' => self::$pmtTypeOptions
            ]
        ];
        $validPMTTypesString = implode(', ', self::$validPmtTypeValues);
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
        $values = $this->getFieldValues();
        $values['model_label'] = 'PMT';
        $this->setFieldValues($values);
    }

    protected function processRequest()
    {
        parent::processRequest();
        $values = $this->getFieldValues();
        $values['id'] = "20LOCER0{$values['serial']}";
        $this->setFieldValues($values);
    }

    public function getEntity(): Equipment
    {
        helper(['array', 'atlas_id']);
        if (!$this->isValid){
            throw new \Exception('To get the equipment you must validate the form first');
        }
        $fieldValues = $this->getFieldValues();
        $equipment = new Equipment($fieldValues);
        $instituteMne = getInstituteMne($equipment->id);
        $owner = (new \App\Models\InstituteModel())->where(sprintf("mne = '%s'", $instituteMne))->first();
        $equipment->owner_id = $owner->id;
        $model = (new \App\Models\ModelModel())->where(sprintf("label = '%s'", $fieldValues['model_label']))->first();
        $equipment->model_id = $model->id;
        $modelParams = [];
        foreach ($model->params as $param){
            $modelParams[$param] = $this->request->getVar($param);
        }
        $equipment->model_params = $modelParams;
        return $equipment;
    }

    public function asEdit(){

        $this->fields['id']['fieldParams']['readOnly'] = null;
        $this->validationRules['id']['rules'] = [
            "permit_empty",
            "if_exist",
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
            sprintf("is_model_not_unique[serial,{%s}]", self::$pmtId)
        ];
        $this->validationRules['serial']['errors'] = [
            "required" => "A serial code is required",
            "max_length" => "Serial max size is {param} characters",
            'regex_match' => 'The serial code must have the first 2 characters as upper case letters and the last 4 as digits.',
            "is_model_not_unique" => "There is no PMT with the serial {value}"
        ];

        $this->resetRules();
    }
}
