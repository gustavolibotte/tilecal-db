<?php

namespace App\Validation\Validators;
use App\Validation\AppFileRules;

class FileValidator extends EntryValidator
{
    public function initialize()
    {
        parent::initialize();

        $this->validationRules["name"]= [
            "rules" => [
                "required",
                sprintf("max_length[%d]", AppFileRules::$maxNameSize),
                "is_unique[files.name]"
            ],
            "errors" => [
                "required" => "You must fill a valid filename.",
                "max_length" => "Name max number of characters is {param}.",
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
    }

    public function getData()
    {
        helper(['array', 'path']);
        $fieldValues = $this->getFieldValues();
        $file = new \App\Entities\File([
            'name' => $fieldValues['name'],
            'path' => $fieldValues['file']->getRealPath(),
            'description' => $fieldValues['description']
        ]);
        return $file;
    }
}
