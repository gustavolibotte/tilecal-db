<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Library\Authenticator;
use App\Library\Messenger;
use App\Library\Time;
use App\Library\AppLogger;
use App\Library\Environment;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */

    public static function authenticator($getShared=true): Authenticator
    {   
        // echo "Getting authenticator service\n";
        if($getShared){
            // echo "Got shared authenticator service\n";
            return static::getSharedInstance('authenticator');
        }
        // echo "Got new authenticator service\n";
        return new Authenticator();
    }

    public static function time($getShared=true)
    {
        if($getShared){
            return static::getSharedInstance('time');
        }
        return new Time();
    }

    public static function messenger($getShared=true)
    {
        if($getShared){
            return static::getSharedInstance('messenger');
        }
        return new Messenger();
    }

    /**
     * Extension of the CodeIgniter Logger class with an extension of
     * the log method.
     *
     * @return AppLogger
     */
    public static function logger($getShared=true): AppLogger
    {   
        if($getShared){
            return static::getSharedInstance('logger');
        }
        return new AppLogger(config('Logger'));
    }

    public static function environment($getShared=true): Environment
    {
        if ($getShared){
            return static::getSharedInstance('environment');
        }
        return new Environment();
    }
}
