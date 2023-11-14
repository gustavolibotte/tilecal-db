<?php

namespace App\Validation;
use ValueError;

class AppFileRules {

    private static int $maxFileSize = 5000000;  //Bytes
    public static int $maxNameSize = 100;
    public static int $maxDescriptionSize = 65535;
    public static int $minDescriptionSize = 10;

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
}