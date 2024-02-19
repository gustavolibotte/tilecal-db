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
        FileRules::class,
        \App\Validation\AtlasIdRules::class,
        \App\Validation\EquipmentRules::class,
        \App\Validation\StringRules::class,
        \App\Validation\TypeSlotRules::class,
        \App\Validation\AppFileRules::class,
        \App\Validation\DatabaseRules::class,
        \App\Validation\EquipmentTypeRules::class,
        \App\Validation\InstituteRules::class
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

    /**
     * Specifies the rules for user input params in
     * equipment CRUD CLI commands.
     *
     * @var array<string, array<string, array>>
     */
    public array $equipCLIParams = [
        'filepath' => [
            'rules' => [
                'required',
                'local_file_exists',
                'string_ends_with[.csv]',
                'local_mime_in[text/csv]'
            ],
            'errors' => [
                'required' => 'Filepath is required',
                'string_ends_with' => 'The file must be a csv. (File name did not end in .csv)',
                'local_file_exists' => 'The given file doesn\'exist',
                'local_mime_in' => 'The file must be a csv. (File structure not identified as a csv)'
            ]
        ],
        'type_name' => [
            'rules' => [
                'required',
                'is_not_unique[equipment_types.name]'
            ],
            'errors' => [
                'required' => 'Equipment type name is required',
                'is_not_unique' => 'Equipment type name {value} is not valid.'
            ]
        ]
    ];
}
