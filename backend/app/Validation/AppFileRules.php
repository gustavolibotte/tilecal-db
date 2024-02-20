<?php

namespace App\Validation;
use ValueError;

class AppFileRules{

    private static int $maxFileSize = 5000000;  //Bytes
    public static int $maxNameSize = 100;
    public static int $maxDescriptionSize = 65535;
    public static int $minDescriptionSize = 10;
    public static string $fileIdRegex = '/^[0-9]$/';
    public static string $nameMaxLength = 100;
    public static string $nameMinLength = 1;
    public static string $nameRegex = '/^[A-Za-z_]+$/';

    /**
     * Checks if the file id value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function file_id_is_sanitized($value, ?string &$error): bool
    {
        helper('string_validation');
        return regex_match($value, Self::$fileIdRegex, 'File Id', $error);
    }

    /**
     * Checks if the file name value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function file_name_is_sanitized($value, ?string &$error): bool
    {
        helper('string_validation');
        $fieldName = 'File name';
        return (
            max_length($value, Self::$nameMaxLength, $fieldName, $error) &&
            min_length($value, Self::$nameMinLength, $fieldName, $error) &&
            regex_match($value, Self::$fileIdRegex, $fieldName, $error)
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