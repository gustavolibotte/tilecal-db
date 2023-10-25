<?php

namespace App\Library\Forms;

class FileForm extends Form{

    public int $maxNameSize = 100;
    public int $maxDescriptionSize = 65535;
    public int $minDescriptionSize = 10;
    public int $maxFileSize = 512;  //MB
    public function initialize(){

        $this->fields = [
            'name' => [
                'id' => 'name',
                'required' => true,
                'helpText' => sprintf("Max of %d characters", $this->maxNameSize),
                'fieldType' => 'input',
                'fieldParams' => [
                    'maxlength' => $this->maxNameSize,
                    'placeholder' => 'Example: 2023_12_12_pmt_52726_test_results'
                ]
            ],
            'file' => [
                'id' => 'file',
                'required' => true,
                'helpText' => sprintf("Max size: %d MB", $this->maxFileSize),
                'fieldType' => 'upload',
                'fieldParams' => [
                    'placeholder' => 'Select a file for upload'
                ]
            ],
            'description' => [
                'id' => 'description',
                'required' => true,
                'helpText' => sprintf("At least %d and max of %d characters",
                                      $this->minDescriptionSize,
                                      $this->maxDescriptionSize),
                'fieldType' => 'textarea',
                'fieldParams' => [
                    'maxlength' => $this->maxDescriptionSize,
                    'rows' => 3,
                    'placeholder' => 'Example: This file contains info on the equipment X tests made on the DD/MM/YYYY'
                ]
            ],
        ];

        $this->validationRules = [
            "name" => [
                "rules" => [
                    "required",
                    sprintf("max_length[%d]", $this->maxNameSize),
                    "is_unique[files.name]"
                ],
                "errors" => [
                    "required" => "You must fill a valid filename.",
                    "max_length" => sprintf("Name max number of characters is %s.", $this->maxNameSize),
                    "is_unique" => "The filename {value} already exists"
                ]
            ],
            "file" => [
                "rules" => [
                    "uploaded[file]", 
                    sprintf("max_size[file,%d]", $this->maxFileSize)
                ],
                "errors" => [
                    "uploaded" => "You must upload a file.",
                    "max_size" => sprintf('Max file size is %d KB', $this->maxFileSize)
                ]
            ],
            "description" => [
                "rules" => [
                    "required",
                    sprintf("max_length[%d]", $this->maxDescriptionSize),
                    sprintf("min_length[%d]", $this->minDescriptionSize),
                ],
                "errors" => [
                    "required" => "A file description is required.",
                    "max_length" => sprintf("Description max number of characters is %d.", $this->maxDescriptionSize),
                    "min_length" => sprintf("Description min number of characters is %d.", $this->minDescriptionSize),
                ]
            ]
        ];
    }

    public function getEntity(){
        helper(['array', 'path']);
        if (!$this->isValid){
            throw new \Exception('To get the equipment you must validate the form first');
        }
        $uploadedFile = $this->request->getFile('file');
        $fieldValues = $this->getFieldValues();
        $file = new \App\Entities\File([
            'name' => $fieldValues['name'],
            'path' => $uploadedFile->getRealPath(),
            'description' => $fieldValues['description']
        ]);
        return $file;
    }
}