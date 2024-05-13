<?php

namespace App\Validation;
use ValueError;

class AppFileRules{

    public static int $maxDescriptionSize = 65535;
    public static int $minDescriptionSize = 10;
    public static int $nameMaxLength = 100;
    public static int $nameMinLength = 1;
    public static string $nameRegex = '/^.*$/';

    public static string $idRegex = '/^[0-9]+$/';

    public static int $pathMaxLength = 300;

    public static string $pathStartsWith = 'https://cernbox.cern.ch';

    /**
     * Checks if the id value is sanitized
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Ignored
     * @return bool True if the value is sanitized
     * @see StringRules::regexMatch
     * @see Validator::validate
     */
    public static function idIsSanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        return StringRules::regexMatch($fieldName, $data, $error, ['regex' => self::$idRegex]);
    }

    /**
     * Checks if the file name value is sanitized
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Ignored
     * @return bool True if the value is sanitized
     * @see StringRules::regexMatch
     * @see StringRules::minLength
     * @see StringRules::maxLength
     * @see Validator::validate
     */
    public static function nameIsSanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        return (
            StringRules::regexMatch($fieldName, $data, $error, ['regex' => self::$nameRegex]) &&
            StringRules::minLength($fieldName, $data, $error, ['length' => self::$nameMinLength]) &&
            StringRules::maxLength($fieldName, $data, $error, ['length' => self::$nameMaxLength])
        );
    }

    /**
     * Checks if the file description value is sanitized
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params Ignored
     * @return bool True if the value is sanitized
     * @see StringRules::minLength
     * @see StringRules::maxLength
     * @see Validator::validate
     */
    public static function descriptionIsSanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        return (
            StringRules::minLength($fieldName, $data, $error, ['length' => self::$minDescriptionSize]) &&
            StringRules::maxLength($fieldName, $data, $error, ['length' => self::$maxDescriptionSize])
        );
    }

    public static function pathIsSanitized(string $fieldName, array $data, string &$error, array $params): bool
    {
        return (
            StringRules::startsWith($fieldName, $data, $error, ['prefix' => self::$pathStartsWith]) &&
            StringRules::maxLength($fieldName, $data, $error, ['length' => self::$pathMaxLength])
        );
    }
}