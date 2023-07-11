<?php
namespace ValidationRules;

class EquipmentRules{

    // MySQL max char count on text type
    public $maxCommentSize = 65535;

    public $validLocations = [
        '175'
    ];

    public $validStatus = [
        'delivered', 
        'tested', 
        'assembled', 
        'disassembled', 
        'broken'
    ];

    public $validPMTTypes = [
        'r11187',
        'r11187',
        'r7877'
    ];
    public $registerIDRules = [];
    public $editIDRules = [];
    public $modelNameRules = [];
    public $locationRules = [];
    public $statusRules = [];
    public $commentsRules = [];
    public $equipmentRegisterRules = [];
    public $equipmentEditRules = [];
    public $pmtRegisterRules = [];


    public function __construct(){
        $this->registerIDRules = [
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
            ]
        ];

        $this->editIDRules = [
            "id" => [
                "rules" => [
                    "required",
                    "is_natural_no_zero",
                    "is_not_unique[equipments.id]"
                ],
                "errors" => [
                    "required" => "You must supply a ID for the equipment.",
                    "is_natural_no_zero" => "The ID must be greater than zero.",
                    "is_not_unique" => "The ID must exist"
                ]
            ]
        ];

        $this->modelNameRules = [
            "model_name" => [
                "rules" => [
                    "required",
                    "is_not_unique[models.name]",
                ],
                "errors" => [
                    "required" => "You must supply a model for the equipment.",
                    "is_not_unique" => "The given model doesn't exist.",
                ]
            ]
        ];

        $validLocationsString = implode(',', $this->validLocations);
        $this->locationRules = [
            "location" => [
                "rules" => [
                    "required",
                    sprintf("in_list[%s]",$validLocationsString),
                ],
                "errors" => [
                    "required" => "You must supply a location for the equipment.",
                    "in_list" => sprintf("The possible locations are %s", $validLocationsString),
                ]
            ]
        ];

        $validStatusString = implode(',', $this->validStatus);
        $this->statusRules = [
            "status" => [
                "rules" => [
                    "required",
                    sprintf("in_list[%s]",$validStatusString),
                ],
                "errors" => [
                    "required" => "You must supply a status for the equipment.",
                    "in_list" => sprintf("The possible statuses are %s", $validStatusString),
                ]
            ]
        ];

        $this->commentsRules = [
            "comments" => [
                "rules" => [
                    sprintf("max_length[%d]", $this->maxCommentSize),
                ],
                "errors" => [
                    "max_length" => sprintf("Description max number of characters is %d.", $this->maxCommentSize),
                ]
            ]
        ];

        $this->equipmentRegisterRules = array_merge(
            $this->registerIDRules,
            $this->modelNameRules,
            $this->locationRules,
            $this->commentsRules,
            $this->statusRules
        );

        $this->equipmentEditRules = array_merge(
            $this->editIDRules,
            $this->modelNameRules,
            $this->locationRules,
            $this->commentsRules,
            $this->statusRules
        );

        $this->pmtRegisterRules = array_merge($this->equipmentEditRules, $this->pmtregister());
    }

    public function pmtRegister(){

        $validPMTTypesString = implode(', ', $this->validPMTTypes);

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

        return $pmtExclusiveRules;
    }
}
