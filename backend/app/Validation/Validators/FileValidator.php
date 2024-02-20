<?php

namespace App\Validation\Validators;
use App\Validation\AppFileRules;
use App\Entities\File;

class FileValidator extends EntryValidator
{
    public array $fileFields = ['file'];

    public function initialize()
    {
        parent::initialize();
        helper('rule_manager');

        $this->updateRules['id'] = [
            'rules' => [
                'required',
                'file_id_is_sanitized',
                'is_not_unique[files.id]'
            ],
            'errors' => [
                'required' => 'Id field is required for files.',
                'is_not_unique' => 'The given file id ({value}) does not exist'
            ]
        ];

        $this->validationRules["name"]= [
            "rules" => [
                "required",
                "file_name_is_sanitized",
                "is_unique[files.name]"
            ],
            "errors" => [
                "required" => "You must fill a valid filename.",
                "max_length" => "Name max number of characters is {param}.",
                "is_unique" => "The filename {value} already exists"
            ]
        ];
        $this->updateRules["name"]= [
            "rules" => [
                "permit_empty",
                'file_name_is_sanitized',
                "is_unique[files.name]"
            ],
            "errors" => [
                "is_unique" => "The filename {value} already exists"
            ]
        ];

        $fileSizeKb = AppFileRules::getMaxFileSize('kB');
        $this->validationRules['file'] = [
            "rules" => [
                "uploaded[file]", 
                sprintf("max_size[file,%d]", $fileSizeKb)
            ],
            "errors" => [
                "uploaded" => "You must upload a file.",
                "max_size" => sprintf('Max file size is %d kB', $fileSizeKb)
            ]
        ];
        $this->updateRules['file'] = remove_rule(
            $this->validationRules['file'],
            'uploaded'
        );

        $this->validationRules['description'] = [
            "rules" => [
                "required",
                sprintf("max_length[%d]", AppFileRules::$maxDescriptionSize),
                sprintf("min_length[%d]", AppFileRules::$minDescriptionSize),
            ],
            "errors" => [
                "required" => "A file description is required.",
                "max_length" => "Description max number of characters is {param}.",
                "min_length" => "Description min number of characters is {param}.",
            ]
        ];
        $this->updateRules['description'] = remove_rule(
            $this->validationRules['description'],
            'required'
        );
        $this->updateRules['description'] = add_rule(
            $this->updateRules['description'],
            'permit_empty',
            where: 'start'
        );
    }

    protected function getData()
    {
        $data = ($this->isEdit)? $this->getDataEdit(): $this->getDataInsert();
        return new File($data);
    }

    public function getDataInsert()
    {
        helper('app_array');
        $fieldValues = $this->getFieldValues();
        $uploadedFile = remove_value('file', $fieldValues);
        $fieldValues['fileObj'] = $uploadedFile;
        return $fieldValues;
    }

    public function getDataEdit()
    {
        $fieldValues = $this->getFieldValues();
        if (array_key_exists('file', $fieldValues)){
            helper('app_array');
            $uploadedFile = remove_value('file', $fieldValues);
            $fieldValues['fileObj'] = $uploadedFile;
        }
        return $fieldValues;
    }
}
