<?php

namespace App\Validation;

class DetectorLocationRules{

    public static $maxNameLength = 5;
    public static $nameRegex = '/^[A-Za-z0-9]+$/';
    public static $detectorLocationIdRegex = '/^[0-9]+$/';

    /**
     * Checks if the id value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function detector_location_id_is_sanitized($value, ?string &$error): bool
    {
        helper('string_validation');
        $fieldName = 'Detector location id';
        return (
            regex_match($value, Self::$detectorLocationIdRegex, $fieldName, $error)
        );
    }

    /**
     * Checks if the name value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function detector_location_name_is_sanitized($value, ?string &$error): bool
    {
        helper('string_validation');
        $fieldName = 'Detector location name';
        return (
            max_length($value, Self::$maxNameLength, $fieldName, $error) &&
            regex_match($value, Self::$nameRegex, $fieldName, $error)
        );
    }
}