<?php

namespace App\Library\Forms;
use App\Validation\AppFileRules;

class FileForm extends Form{

    public function initialize(){

        parent::initialize();
        $this->fields = [
            'name' => [
                'id' => 'name',
                'required' => true,
                'helpText' => sprintf("Max of %d characters", AppFileRules::$maxNameSize),
                'fieldType' => 'input',
                'fieldParams' => [
                    'maxlength' => AppFileRules::$maxNameSize,
                    'placeholder' => 'Example: 2023_12_12_pmt_52726_test_results'
                ]
            ],
            'file' => [
                'id' => 'file',
                'required' => true,
                'helpText' => sprintf("Max size: %d kB", AppFileRules::getMaxFileSize('kB')),
                'fieldType' => 'upload',
                'fieldParams' => [
                    'placeholder' => 'Select a file for upload'
                ]
            ],
            'description' => [
                'id' => 'description',
                'required' => true,
                'helpText' => sprintf("At least %d and max of %d characters",
                                      AppFileRules::$minDescriptionSize,
                                      AppFileRules::$maxDescriptionSize),
                'fieldType' => 'textarea',
                'fieldParams' => [
                    'maxlength' => AppFileRules::$maxDescriptionSize,
                    'rows' => 3,
                    'placeholder' => 'Example: This file contains info on the equipment X tests made on the DD/MM/YYYY'
                ]
            ],
        ];
    }
}