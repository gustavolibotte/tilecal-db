<?php

namespace App\Library;

class Time{

    private \CodeIgniter\I18n\Time $requestTimestamp;

    public function __construct()
    {
        $this->requestTimestamp = \CodeIgniter\I18n\Time::now();
    }

    /**
     * Returns the timestamp on which the request was received at the server.
     * This is classe is initialized by RequestTime Filter.
     * 
     * @return \CodeIgniter\I18n\Time
     */
    public function getRequestTimestamp(): \CodeIgniter\I18n\Time
    {
        return $this->requestTimestamp;
    }
}
