<?php

namespace Config;

use CodeIgniter\Config\Factory as BaseFactory;

class Factory extends BaseFactory
{
    public $filters = [
        'instanceOf' => FilterInterface::class
    ];

    public $library = [
        'path' => 'Libraries',
    ];
}
