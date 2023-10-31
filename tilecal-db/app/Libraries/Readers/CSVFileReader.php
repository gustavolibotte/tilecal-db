<?php

namespace App\Library\Readers;
use CodeIgniter\Files\Exceptions\FileException;
use Iterator;
use Throwable;

class CSVFileReader implements Iterator{

    public string $filepath;
    private $handle = null;
    private ?array $header = null;
    private int $iRow = -1;

    private array $row = [];

    private string $separator;

    private bool $hasHeader;


    public function __construct(string $filepath, string $separator=',', bool $hasHeader=true)
    {
        $this->filepath = $filepath;
        $this->separator = $separator;
        $this->hasHeader = $hasHeader;
    }

    public function rewind(): void
    {
        if (!is_null($this->handle)){
            $this->close();
        } else{
            $this->clear();
        }
        if (($this->handle = fopen($this->filepath, "r")) !== FALSE) {
            if ($this->hasHeader) {
                $this->header = fgetcsv($this->handle, null, $this->separator);
            }
            $this->next();
        } else{
            throw new FileException("Could not open file");
        }
    }

    public function valid(): bool
    {
        try{
            if (!is_null($this->handle)){
                return !feof($this->handle);
            } else{
                return false;
            }
        } catch (Throwable $e){
            log_message('error',
                '{errorMsg} - {filepath}',
                [
                    'errorMsg' => $e->getMessage(),
                    'filepath' => $this->filepath
            ]);
            $this->close();
            return false;
        }
    }

    public function key(): int
    {
        return $this->iRow;
    }

    public function current(): array
    {
        return $this->row;
    }

    public function next()
    {
        try{
            $row = fgetcsv($this->handle);
            if ($row === FALSE) {
                $this->close();
                return null;
            }
            foreach ($row as $iCol=>&$value){
                if (empty($value)){
                    $value = null;
                }
                $this->row[$this->header[$iCol]] = $value;
            }
            $this->iRow++;
        }  catch (Throwable $e){
            log_message('error',
                '{errorMsg} - {filepath}',
                [
                    'errorMsg' => $e->getMessage(),
                    'filepath' => $this->filepath
            ]);
            $this->close();
        }
    }

    public function close(): void
    {
        if (!is_null($this->handle)){
            fclose($this->handle);
        }
        $this->clear();
    }

    protected function clear(): void
    {
        $this->header = [];
        $this->handle = null;
        $this->iRow = -1;
        $this->row = [];
    }
}