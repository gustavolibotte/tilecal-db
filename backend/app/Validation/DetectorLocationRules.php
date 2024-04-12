<?php

namespace App\Validation;

class DetectorLocationRules{

    public static $maxNameLength = 5;
    public static $nameRegex = '/^[A-Za-z0-9]+$/';

    /**
     * Checks if the detector location name value is sanitized
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Ignored
     * @return bool True if the value is sanitized
     */
    public function detectorLocationNameIsSanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        return  (
            StringRules::maxLength($fieldName, $data, $error, ['length' => self::$maxNameLength]) &&
            StringRules::regexMatch($fieldName, $data, $error, ['regex' => self::$nameRegex])
        );
    }
}