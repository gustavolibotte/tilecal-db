<?php

namespace App\Validation;

class InstituteRules extends RuleSet{

    public static $maxNameLength = 300;
    public static $nameRegex = '/^[A-Za-z0-9 ]+$/';
    public static $mneLength = 3;
    public static $mneRegex = '/^[A-Z]+$/';

    /**
     * Checks if the id value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function institute_id_is_sanitized($value, ?string &$error): bool
    {
        if (!preg_match('/^[0-9]+$/', $value)){
            $error = $this->regexErrorMessage('Institute id');
            return false;
        }
        return true;
    }

    /**
     * Checks if the name value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function institute_name_is_sanitized($value, ?string &$error): bool
    {

        if (!(strlen($value) <= Self::$maxNameLength)){
            $error = $this->maxLengthMessage('Institute name', Self::$maxNameLength);
            return false;
        }

        if (!preg_match(Self::$nameRegex, $value)){
            $error = $this->regexErrorMessage('Institute name');
            return false;
        }
        return true;
    }

    /**
     * Checks if the mne value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function institute_mne_is_sanitized($value, ?string &$error): bool
    {

        if (!(strlen($value) === Self::$mneLength)){
            $error = $this->exactLengthMessage('Institute mne', Self::$maxNameLength);
            return false;
        }

        if (!preg_match(Self::$mneRegex, $value)){
            $error = $this->regexErrorMessage('Institute mne');
            return false;
        }
        return true;
    }
}