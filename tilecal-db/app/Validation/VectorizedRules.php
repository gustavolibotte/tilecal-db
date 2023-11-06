<?php

namespace App\Validation;

class VectorizedRules
{
    /**
     * Checks if the string ends with a specific substring
     *
     * @param $value  Value given by CodeIgniter validator
     * @param string $params Substring to check
     * @param array $data Ignored, it exists for compatibility reasons only
     * @param ?string &$error Ignored, it exists for compatibility reasons only
     * 
     * @return bool True if the value ends with the substring in $params
     */ 
    public function vec_regex_match($value, string $params, array $data, ?string &$error): bool
    {   
        $regex = $params;
        $return = true;
        $baseErrorMessage = '';
        $error = '';
        if (is_array($value)){
            foreach($value as $i=>$val){
                if (!preg_match($regex, $val, $matches)){
                    $return = false;
                    $error .= "Row";
                }
            }
        }
        return str_ends_with($value, $params);
    }

    protected function __vec_regex_match__($value, $regex){}
}
