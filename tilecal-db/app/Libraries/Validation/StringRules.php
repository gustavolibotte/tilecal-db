<?php

namespace App\Library\Validation;

class StringRules{

    public function string_ends_with($value, string $params, array $data, ?string &$error): bool
    {   
        return str_ends_with($value, $params);
    }

    public function string_starts_with($value, string $params, array $data, ?string &$error): bool
    {   
        return str_starts_with($value, $params);
    }
}