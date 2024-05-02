<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var string[]
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------

    public array $fileGetRules = [
        'id' => [
            '\App\Validation\GeneralRules::permitEmpty' => [],
            '\App\Validation\AppFileRules::idIsSanitized' => []
        ],
        'name' => [
            '\App\Validation\GeneralRules::permitEmpty' => [],
            '\App\Validation\AppFileRules::nameIsSanitized' => []
        ],
        'onlyDeleted' => [
            '\App\Validation\GeneralRules::isBool' => [
                'params' => [
                    'cast' => true
                ]
            ]
        ],
        'relatedEquipmentId' => [
            '\App\Validation\GeneralRules::permitEmpty' => [],
            '\App\Validation\EquipmentRules::idIsSanitized' => []
        ]
    ];

    public array $fileCreateRules = [
        'name' => [
            '\App\Validation\GeneralRules::required' => [],
            '\App\Validation\AppFileRules::nameIsSanitized' => [],
            '\App\Validation\DatabaseRules::isUnique' => [
                'params' => [
                    'table' => 'files',
                    'column' => 'name'
                ]
            ]
        ],
        'path' => [
            '\App\Validation\GeneralRules::required' => [],
            '\App\Validation\AppFileRules::pathIsSanitized' => [],
            '\App\Validation\DatabaseRules::isUnique' => [
                'params' => [
                    'table' => 'files',
                    'column' => 'path'
                ]
            ]
        ],
        'description' => [
            '\App\Validation\GeneralRules::required' => [],
            '\App\Validation\AppFileRules::descriptionIsSanitized' => []
        ]
    ];

    public array $fileUpdateRules = [
        'id' => [
            '\App\Validation\GeneralRules::required' => [],
            '\App\Validation\AppFileRules::idIsSanitized' => [],
            '\App\Validation\DatabaseRules::isNotUnique' => [
                'params' => [
                    'table' => 'files',
                    'column' => 'id'
                ]
            ]
        ],
        'name' => [
            '\App\Validation\GeneralRules::removeEmpty' => [],
            '\App\Validation\AppFileRules::nameIsSanitized' => [],
            '\App\Validation\DatabaseRules::isUnique' => [
                'params' => [
                    'table' => 'files',
                    'column' => 'name'
                ]
            ]
        ],
        'path' => [
            '\App\Validation\GeneralRules::removeEmpty' => [],
            '\App\Validation\AppFileRules::pathIsSanitized' => [],
            '\App\Validation\DatabaseRules::isUnique' => [
                'params' => [
                    'table' => 'files',
                    'column' => 'path'
                ]
            ]
        ],
        'description' => [
            '\App\Validation\GeneralRules::removeEmpty' => [],
            '\App\Validation\AppFileRules::descriptionIsSanitized' => []
        ]
    ];
}
