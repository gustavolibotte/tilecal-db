<?php

namespace App\Validation;

class FileRules{

    /**
     * Checks if the file doesn't exceed a maximum.
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params The params array must have the following keys:
     * 
     * - maxSize: int, the maximum size in bytes
     * 
     * @return bool True if the file doesn't exceed the maximum size
     * @see Validator:validate 
     */
    public static function maxSize(string $fieldName, array $data, string &$error, array $params): bool
    {
        $maxSize = $params['maxSize'];
        $file = $data[$fieldName];
        if ($file->getSize() > $maxSize){
            $error = sprintf(
                'The file %s is too large. The maximum size is %s',
                $file->getClientName(),
                $maxSize
            );
            return false;
        }
        return true;
    }

    /**
     * Checks if the file has a valid mime type.
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params The params array must have the following keys:
     * 
     * - mimeTypes: array, the valid mime types
     * 
     * @return bool True if the file has a valid mime type
     * @see Validator:validate
     */
    public static function mimeIn(string $fieldName, array $data, string &$error, array $params): bool
    {
        $mimeTypes = $params['mimeTypes'];
        $file = $data[$fieldName];
        if (!in_array($file->getMimeType(), $mimeTypes)){
            $error = sprintf(
                'The file %s has an invalid mime type. The valid types are %s',
                $file->getClientName(),
                implode(', ', $mimeTypes)
            );
            return false;
        }
        return true;
    }

    /**
     * Checks if the file has a valid extension.
     * @param string $fieldName Name of the field to check
     * @param array $data Data array to check
     * @param string $error Error message to return if the check fails
     * @param array $params The params array must have the following keys:
     * 
     * - exts: array, the valid extensions
     * 
     * @return bool True if the file has a valid extension
     * @see Validator:validate
     */
    public static function extIn(string $fieldName, array $data, string &$error, array $params): bool
    {
        $exts = $params['exts'];
        $file = $data[$fieldName];
        if (!in_array($file->guessExtension(), $exts)){
            $error = sprintf(
                'The file %s has an invalid extension. The valid extensions are %s',
                $file->getClientName(),
                implode(', ', $exts)
            );
            return false;
        }
        return true;
    }
}