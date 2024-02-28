<?php

namespace App\Validation;

class HistRules{

    public static $idRegex = '/^[0-9]+$/';

    /**
     * Checks if the id value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function hist_id_is_sanitized($value, ?string &$error): bool
    {
        helper('string_validation');
        $fieldName = 'Hist id';
        return (
            regex_match($value, Self::$idRegex, $fieldName, $error)
        );
    }
}