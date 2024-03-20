<?php

namespace App\Validation\Validators;

class BatchRegisterValidator extends EntryValidator
{
    public static int $maxFileSize = 50;

    public array $fileFields = [
        'file'
    ];
    public function initialize()
    {
        $this->validationRules['type_name'] = [
            "rules" => [
                "required",
                "equipment_type_name_is_sanitized",
                "is_not_unique[equipment_types.name]",
            ],
            "errors" => []
        ];

        $notCsvMsg = "Only csv files are supported for batch registering.";
        $this->validationRules['file'] = [
            "rules" => [
                "uploaded[file]", 
                sprintf("max_size[file,%d]", Self::$maxFileSize),
                "mime_in[file,text/csv]",
                "ext_in[file,csv]"
            ],
            "errors" => [
                "uploaded" => "You must upload a file.",
                "max_size" => sprintf("Max file size is %d kB", Self::$maxFileSize),
                "mime_in" => $notCsvMsg,
                "ext_in" => $notCsvMsg
            ]
        ];
    }
}
