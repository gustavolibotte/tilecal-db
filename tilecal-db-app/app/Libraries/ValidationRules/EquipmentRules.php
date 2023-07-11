<?php
namespace ValidationRules;

class EquipmentRules{

    // MySQL max char count on text type
    public static $maxCommentSize = 65535;

    public static $validLocations = [
        '175'
    ];

    public static $validStatus = [
        'delivered', 
        'tested', 
        'assembled', 
        'disassembled', 
        'broken'
    ];

    public static $validPMTTypes = [
        'r11187',
        'r11187',
        'r7877'
    ];

    public static function register(){
        
        $validLocationsString = implode(', ', EquipmentRules::$validLocations);
        $validStatusString = implode(', ', EquipmentRules::$validStatus);

        $equipmentRegisterRules = [
            "id" => [
                "rules" => [
                    "required",
                    "is_natural_no_zero",
                    "is_unique[equipments.id]"
                ],
                "errors" => [
                    "required" => "You must supply a ID for the equipment.",
                    "is_natural_no_zero" => "The ID must be greater than zero.",
                    "is_unique" => "This ID already exists"
                ]
            ],
            "model_name" => [
                "rules" => [
                    "required",
                    "is_not_unique[models.name]",
                ],
                "errors" => [
                    "required" => "You must supply a model for the equipment.",
                    "is_not_unique" => "The given model doesn't exist.",
                ]
            ],
            "location" => [
                "rules" => [
                    "required",
                    sprintf("in_list[%s]",$validLocationsString),
                ],
                "errors" => [
                    "required" => "You must supply a location for the equipment.",
                    "in_list" => sprintf("The possible locations are %s", $validLocationsString),
                ]
            ],
            "status" => [
                "rules" => [
                    "required",
                    sprintf("in_list[%s]",$validStatusString),
                ],
                "errors" => [
                    "required" => "You must supply a status for the equipment.",
                    "in_list" => sprintf("The possible statuses are %s", $validStatusString),
                ]
            ],
            "comments" => [
                "rules" => [
                    sprintf("max_length[%s]", strval(EquipmentRules::$maxCommentSize)),
                ],
                "errors" => [
                    "max_length" => sprintf("Description max number of characters is %d.", strval(EquipmentRules::$maxCommentSize)),
                ]
            ]
        ];
        
        return $equipmentRegisterRules;
    }

    public static function pmtRegister(){

        $validPMTTypesString = implode(', ', EquipmentRules::$validPMTTypes);

        $pmtExclusiveRules = [
            "beta" => [
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
            ],
            "nominal_hv" => [
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
            ],
            "qe" => [
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
            ],
            "type" => [
                "rules" => [
                    "required",
                    sprintf("in_list[%s]",$validPMTTypesString),
                ],
                "errors" => [
                    "required" => "You must supply a PMT model type for the equipment.",
                    "in_list" => sprintf("The possible PMT model types are %s", $validPMTTypesString),
                ]
            ]
        ];

        $pmtRegisterRules = array_merge(
            EquipmentRules::register(),
            $pmtExclusiveRules
        );

        return $pmtRegisterRules;
    }
}
