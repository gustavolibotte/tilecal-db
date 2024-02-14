<?php

namespace App\Library;

class Environment{

    public function __construct(){}

    public function getValue(): string
    {
        return env('CI_ENVIRONMENT');
    }

    public function isSafe(): bool
    {
        return ($this->isDev() ||
            $this->isLocal());
    }

    public function isDev(): bool
    {
        return $this->getValue() == DEV_ENV;
    }

    public function isProd(): bool
    {
        return $this->getValue() == PROD_ENV;
    }

    public function isLocal(): bool
    {
        return $this->getValue() == LOCAL_ENV;
    }
}