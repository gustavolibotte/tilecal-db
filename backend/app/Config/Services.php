<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Library\User;
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

    public static function time($getShared=true)
    {
        if($getShared){
            return static::getSharedInstance('time');
        }
        return new Time();
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

    public static function user($getShared=true): ?User
    {   
        if ($getShared){
            return static::getSharedInstance('user');
        }
        return new User();
    }
}
