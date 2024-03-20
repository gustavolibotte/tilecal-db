<?php

namespace App\Library;

class Reader{

    public static function readCsv(string $filepath, bool $hasHeader=true): array
    {
        $allRows = array();
        try{
            if (($handler = fopen($filepath, "r")) !== FALSE) {
                if($hasHeader){
                    self::loadCsvWithHeader($handler, $allRows);
                } else {
                    self::loadCsvWithoutHeader($handler, $allRows);
                }
                
            }
        } finally{
            fclose($handler);
        }
        return $allRows;
    }

    private static function loadCsvWithHeader($fileHandler, array &$allRows)
    {
        $header = fgetcsv($fileHandler);
        while ($row = fgetcsv($fileHandler)) {
            $allRows[] = array_combine($header, $row);
        }
    }

    private static function loadCsvWithoutHeader($fileHandler, array &$allRows)
    {
        while ($row = fgetcsv($fileHandler)) {
            $allRows[] = $row;
        }
    }
}