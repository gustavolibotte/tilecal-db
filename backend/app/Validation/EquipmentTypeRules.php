<?php

namespace App\Validation;

class EquipmentTypeRules{

    public static $maxNameSize = 30;
    public static $baseNameRegex = '/^[A-Za-z0-9 ]+$/';

    /**
     * Checks if the name value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function equipment_type_name_is_sanitized($value, ?string &$error): bool
    {
        if (!(strlen($value) <= Self::$maxNameSize)){
            $error = sprintf(
                'Equipment type_name cannot exceed %d characters in length.',
                Self::$maxNameSize
            );
            return false;
        }
        if (!preg_match(Self::$baseNameRegex, $value)){
            $error = 'Equipment type_name is not in the correct format';
            return false;
        }
        return true;
    }
}