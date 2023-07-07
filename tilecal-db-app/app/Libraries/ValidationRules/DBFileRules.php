<?php
namespace ValidationRules;

class DBFileRules{

    public static $maxDescriptionSize = 65535;
    public static $minDescriptionSize = 30;
    public static $maxFilenameSize = 50;
    public static $maxFileSize = 15000;

    public static function upload(){
    
        $fileUploadRules = [
            "name" => [
                "rules" => [
                    "required",
                    sprintf("max_length[%s]", strval(DBFileRules::$maxFilenameSize)),
                ],
                "errors" => [
                    "required" => "You must fill a valid filename.",
                    "max_length" => sprintf("Name max number of characters is %s.", strval(DBFileRules::$maxFilenameSize)),
                ]
            ],
            "file" => [
                "rules" => [
                    "uploaded[file]", 
                    sprintf("max_size[file,%s]", DBFileRules::$maxFileSize)
                ],
                "errors" => [
                    "uploaded" => "You must upload a file.",
                    "max_size" => sprintf('Max file size is %s KB', DBFileRules::$maxFileSize)
                ]
            ],
            "description" => [
                "rules" => [
                    "required",
                    sprintf("max_length[%s]", strval(DBFileRules::$maxDescriptionSize)),
                    sprintf("min_length[%s]", strval(DBFileRules::$minDescriptionSize)),
                ],
                "errors" => [
                    "required" => "A file description is required.",
                    "max_length" => sprintf("Description max number of characters is %s.", strval(DBFileRules::$maxDescriptionSize)),
                    "min_length" => sprintf("Description min number of characters is %s.", strval(DBFileRules::$minDescriptionSize)),
                ]
            ]
        ];
        
        return $fileUploadRules;
    }
}
