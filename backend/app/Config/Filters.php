<?php

namespace Config;

use App\Filters\ActionAuthorized;
use App\Filters\APIAuthentication;
use App\Filters\AppHeaders;
use App\Filters\Authentication;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;
use App\Filters\RequestTime;
use App\Filters\Corsa;

class Filters extends BaseConfig
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'request_time'  => RequestTime::class,
        'cors'          => Corsa::class,
        'auth' => Authentication::class,
        'action' => ActionAuthorized::class,
        'api_auth' => APIAuthentication::class,
        'app_headers' => AppHeaders::class
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     */
    public array $globals = [
        'before' => [
            // 'auth',
            // 'honeypot',
            'csrf' => ['except' => 'api/*'],
            'request_time',
            // 'invalidchars',
        ],
        'after' => [
            'toolbar' => ['except' => 'api/*'],
            'cors'
            // 'honeypot',
            // 'secureheaders',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'post' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don’t expect could bypass the filter.
     */
    public array $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     */
    public array $filters = [];
}
