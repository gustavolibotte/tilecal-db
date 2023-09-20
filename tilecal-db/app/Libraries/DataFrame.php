<?php

namespace App\Library;

class DataFrame{

    public array $data;
    public array $columns;

    public function __construct(array $data=[], array $columns=[])
    {
        $this->data = $data;
        $this->columns = $columns;
    }

    public function unique(string $col, bool $sorted=true): array
    {
        $unique = array();
        foreach ($this->data as $row){
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

    public static function fromCSV(string $filepath, array $colRename=[], array|bool $nullOnEmpty=true): Self
    {
        helper(['app_array']);
        $allRows = array();
        if (($handle = fopen($filepath, "r")) !== FALSE) {
            try{
                $header = fgetcsv($handle);
                $header = remap_values($header, $colRename);
                while (($row = fgetcsv($handle)) !== FALSE) {
                    $row = array_combine($header, $row);
                    Self::nullOnEmpty($row, $nullOnEmpty);
                    $allRows[] = $row;
                }
            } finally{
                fclose($handle);
            }
        }
        return new Self($allRows, $header);
    }

    protected static function nullOnEmpty(array &$row, array|bool $nullOnEmpty)
    {
        if (empty($nullOnEmpty)){
            return null;
        }
        if (is_array($nullOnEmpty)){
            foreach ($nullOnEmpty as $col){
                if (empty($row[$col])){
                    $row[$col] = null;
                }
            }
        } else{
            foreach($row as &$value){
                if (empty($value)){
                    $value = null;
                }
            }
        }
    }
}