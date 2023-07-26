<?php

namespace App\Library\ErrorUtils;

class ErrorData{


    public static function get401Data(){
        $errorData = [
            'header' => '401: Unauthorized',
            'text' => 'You are not authorized to do this action. If you think this is an error please contact the system administrators.'
        ];
        return $errorData;
    }
}
