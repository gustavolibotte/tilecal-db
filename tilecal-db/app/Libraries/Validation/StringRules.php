<?php

namespace App\Library\Validation;

class StringRules{

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
    public function string_ends_with($value, string $params, array $data, ?string &$error): bool
    {   
        return str_ends_with($value, $params);
    }

    /**
     * Checks if the string starts with a specific substring
     *
     * @param $value  Value given by CodeIgniter validator
     * @param string $params Substring to check
     * @param array $data Ignored, it exists for compatibility reasons only
     * @param ?string &$error Ignored, it exists for compatibility reasons only
     * 
     * @return bool True if the value starts with the substring in $params
     */ 
    public function string_starts_with($value, string $params, array $data, ?string &$error): bool
    {   
        return str_starts_with($value, $params);
    }
}