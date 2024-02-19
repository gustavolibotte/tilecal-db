<?php

namespace App\Validation;
use ValueError;

class AppFileRules {

    private static int $maxFileSize = 5000000;  //Bytes
    public static int $maxNameSize = 100;
    public static int $maxDescriptionSize = 65535;
    public static int $minDescriptionSize = 10;

    public static string $fileIdRegex = '/^[0-9]$/';

    /**
     * Checks if the file id value is sanitized
     * @param $value  Value given by CodeIgniter validator
     * @param ?string &$error Ignored
     */
    public function file_id_is_sanitized($value, ?string &$error): bool
    {
        if (!preg_match(Self::$fileIdRegex, $value)){
            $error = 'File id is not in the correct format';
            return false;
        }
        return true;
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