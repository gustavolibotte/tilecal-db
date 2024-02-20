<?php

namespace App\Validation;

class InstituteRules{

    public static $idRegex = '/^[0-9]+$/';
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
        helper('string_validation');
        $fieldName = 'Institute id';
        return (
            regex_match($value, Self::$idRegex, $fieldName, $error)
        );
    }

    /**
     * Checks if the name value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function institute_name_is_sanitized($value, ?string &$error): bool
    {

        helper('string_validation');
        $fieldName = 'Institute name';
        return (
            max_length($value, Self::$maxNameLength, $fieldName, $error) &&
            regex_match($value, Self::$nameRegex, $fieldName, $error)
        );
    }

    /**
     * Checks if the mne value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function institute_mne_is_sanitized($value, ?string &$error): bool
    {

        helper('string_validation');
        $fieldName = 'Institute mne';
        return (
            exact_length($value, Self::$mneLength, $fieldName, $error) &&
            regex_match($value, Self::$mneRegex, $fieldName, $error)
        );
    }
}