<?php

namespace App\Validation;

class InstituteRules{

    public static $idRegex = '/^[0-9]+$/';
    public static $maxNameLength = 300;
    public static $nameRegex = '/^[A-Za-z0-9 ]+$/';
    public static $mneLength = 3;
    public static $mneRegex = '/^[A-Z]+$/';

    /**
     * Checks if the name value is sanitized
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Ignored
     * @return bool True if the value is sanitized
     */
    public static function nameIsSanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        $approved =  (
            StringRules::maxLength($fieldName, $data, $error, ['length' => self::$maxNameLength]) &&
            StringRules::regexMatch($fieldName, $data, $error, ['regex' => self::$nameRegex])
        );
        $error = str_replace('mne', 'Insitute MNE', $error);
        return $approved;
    }

    /**
     * Checks if the MNE value is sanitized
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Ignored
     */
    public function mneIsSanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        $approved =  (
            StringRules::exactLength($fieldName, $data, $error, ['length' => self::$mneLength]) &&
            StringRules::regexMatch($fieldName, $data, $error, ['regex' => self::$mneRegex])
        );
        $error = str_replace('mne', 'Insitute MNE', $error);
        return $approved;
    }
}