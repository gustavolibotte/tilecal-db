<?php

namespace App\Validation;

class EquipmentTypeRules extends RuleSet{

    public static $maxNameLength = 30;
    public static $baseNameRegex = '/^[A-Za-z0-9 ]+$/';

    /**
     * Checks if the name value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function equipment_type_name_is_sanitized($value, ?string &$error): bool
    {
        if (!(strlen($value) <= Self::$maxNameLength)){
            $error = $this->maxLengthMessage('Equipment type name', Self::$maxNameLength);
            return false;
        }
        if (!preg_match(Self::$baseNameRegex, $value)){
            $error = $this->regexErrorMessage('Equipment type name');
            return false;
        }
        return true;
    }

    /**
     * Checks if the type_slot value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function equipment_type_slot_id_is_sanitized($value, ?string &$error): bool
    {
        if (!preg_match('/^[0-9]+$/', $value)){
            $error = $this->regexErrorMessage('Equipment Type slot id');
            return false;
        }
        return true;
    }

    /**
     * Checks if the type_slot_name value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function equipment_type_slot_name_is_sanitized($value, ?string &$error): bool
    {
        if (!preg_match('/^[A-Za-z0-9 ]+$/', $value)){
            $error = $this->regexErrorMessage('Equipment type slot name');
            return false;
        }
        return true;
    }
}