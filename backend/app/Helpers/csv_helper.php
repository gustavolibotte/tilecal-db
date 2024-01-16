<?php

function read_csv(string $filepath): array
{
    $allRows = array();
        try{
            if (($fileHandler = fopen($filepath, "r")) !== FALSE) {
                $header = fgetcsv($fileHandler);
                while ($row = fgetcsv($fileHandler)) {
                    $allRows[] = array_combine($header, $row);
                }
                
            }
        } finally{
            fclose($fileHandler);
        }
        return $allRows;
}

function unique_values(string $col, array $data, bool $sorted=true): array
{
    $unique = array();
    foreach ($data as $row){
        if (in_array($row[$col], $unique)){
            continue;
        }
        $unique[] = $row[$col];
    }
    if($sorted){
        sort($unique);
    }
    return $unique;
}