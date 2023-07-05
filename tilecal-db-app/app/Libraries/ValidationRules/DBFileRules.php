<?php
namespace ValidationRules;

class DBFileRules{

    # Max Number of charahters supported on MySQL TEXT datatype
    public $maxDescriptionSize = 65535;
    public $minDescriptionSize = 30;
    public $maxFilenameSize = 50;

    public $supportedMimes = [
        "application/pdf"
    ];

    public $supportedMimesFormatted = [
        "PDF"
    ];
    public function upload(){
    
        $fileUploadRules = [
            "name" => [
                "rules" => [
                    "required",
                    sprintf("max_length[%s]", strval($this->maxFilenameSize)),
                ],
                "errors" => [
                    "required" => "You must fill a valid filename.",
                    "max_length" => sprintf("Name max number of characters is %s.", strval($this->maxFilenameSize)),
                ]
            ],
            "file" => [
                "rules" => [
                    "uploaded[file]", 
                    sprintf("mime_in[file,%s]", implode(",", $this->supportedMimes)),
                    "max_size[file,15000]"
                ],
                "errors" => [
                    "uploaded" => "You must upload a file.",
                    "mime_in" => sprintf("The uploaded file is not supported.\r\nSupported types: %s", implode(",", $this->supportedMimesFormatted))
                ]
                ],
            "description" => [
                "rules" => [
                    "required",
                    sprintf("max_length[%s]", strval($this->maxDescriptionSize)),
                    sprintf("min_length[%s]", strval($this->minDescriptionSize)),
                ],
                "errors" => [
                    "required" => "A file description is required.",
                    "max_length" => sprintf("Description max number of characters is %s.", strval($this->maxDescriptionSize)),
                    "min_length" => sprintf("Description min number of characters is %s.", strval($this->minDescriptionSize)),
                ]
            ]
        ];
        
        return $fileUploadRules;
    }
}
