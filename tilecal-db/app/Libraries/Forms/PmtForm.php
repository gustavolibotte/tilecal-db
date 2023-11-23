<?php

namespace App\Library\Forms;
use App\Validation\Validators\PmtValidator;

class PmtForm extends EquipmentForm{

    public function initialize()
    {   
        parent::initialize();
        // Alter fields to follow custom PMT rules

        $this->fields['id']['fieldType'] = 'hidden';

        $this->fields['serial']['required'] = true;
        $this->fields['serial']['helpText'] .= ' For PMTs the id is automatically built from the serial number';
        $this->fields['serial']['helpText'] .= sprintf(' (Max size of %d characters)', PmtValidator::$serialMaxSize);
        $this->fields['serial']['fieldParams']['maxlength'] = PmtValidator::$serialMaxSize;
        $this->fields['serial']['fieldParams']['placeholder'] = str_repeat('X', PmtValidator::$serialMaxSize);
        $this->fields['serial']['placeholder'] = str_repeat('X', PmtValidator::$serialMaxSize);

        $this->fields['beta'] = [
            'id' => 'beta',
            'required' => false,
            'fieldType' => 'input',
            'helpText' => 'Beta must be a postive number',
            'fieldParams' => []
        ];

        $this->fields['nominal_hv'] = [
            'id' => 'nominal_hv',
            'label' => 'Nominal HV',
            'required' => false,
            'fieldType' => 'input',
            'helpText' => 'Nominal HV must be a postive number',
            'fieldParams' => []
        ];

        $this->fields['qe'] = [
            'id' => 'qe',
            'label' => 'QE',
            'required' => false,
            'fieldType' => 'input',
            'helpText' => 'QE must be a postive number',
            'fieldParams' => []
        ];

        $this->fields['model'] = [
            'id' => 'model',
            'required' => false,
            'fieldType' => 'dropdown',
            'fieldParams' => [
                'options' => array_merge(
                    get_blank_dropdown(),
                    array_combine(PmtValidator::$validPmtModelValues, PmtValidator::$validPmtModelValues)
                )
            ]
        ];

        $this->fields['phase1_position'] = [
            'id' => 'phase1_position',
            'label' => 'Phase 1 Position',
            'required' => false,
            'fieldType' => 'dropdown',
            'helpText' => 'PMT position inside the old Phase 1 Drawer',
            'placeholder' => sprintf(
                'A number between %d to %d',
                PmtValidator::$smallestPmtPosition,
                PmtValidator::$biggestPmtPosition
            ),
            'fieldParams' => [
                'options' => array_merge(
                    get_blank_dropdown(),
                    range(PmtValidator::$smallestPmtPosition, PmtValidator::$biggestPmtPosition)
                )
            ]
        ];
        $this->commentsEnd();
    }
}
