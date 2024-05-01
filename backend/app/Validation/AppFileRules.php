<?php

namespace App\Validation;
use ValueError;

class AppFileRules{

    private static int $maxFileSize = 5000000;  //Bytes
    public static int $maxDescriptionSize = 65535;
    public static int $minDescriptionSize = 10;
    public static int $nameMaxLength = 100;
    public static int $nameMinLength = 1;
    public static string $nameRegex = '/^[A-Za-z_]+$/';

    public static string $idRegex = '/^[0-9]+$/';

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
            StringRules::minLength($fieldName, $data, $error, ['min' => self::$nameMinLength]) &&
            StringRules::maxLength($fieldName, $data, $error, ['max' => self::$nameMaxLength])
        );
    }

    /**
     * Checks if the file description value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function file_description_is_sanitized($value, ?string &$error): bool
    {
        $fieldName = 'File description';
        return (
            max_length($value, Self::$maxDescriptionSize, $fieldName, $error) &&
            min_length($value, Self::$minDescriptionSize, $fieldName, $error)
        );
    }

    public static function getMaxFileSize(string $size): int
    {
        if ($size === 'B' || $size === 'Bytes') {
            return Self::$maxFileSize;
        } elseif ($size === 'kB'){
            return Self::$maxFileSize/1000;
        } elseif ($size === 'MB'){
            return Self::$maxFileSize/1000000;
        }
        throw new ValueError(sprintf('%s not valid for $size', $size));
    }

    public function local_mime_in($value, string $params, array $data, ?string &$error): bool
    {
        $filepath = $value;
        $validMimes = explode(',', $params);
        $fileMime = mime_content_type($filepath);
        return in_array($fileMime, $validMimes);
    }

    public function local_file_exists($value): bool
    {
        $filepath = $value;
        return file_exists($filepath);
    }
}