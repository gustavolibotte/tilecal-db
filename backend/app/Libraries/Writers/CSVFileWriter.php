<?php

namespace App\Library\Writers;
use CodeIgniter\Files\Exceptions\FileException;
use RuntimeException;
use Throwable;

class CSVFileWriter{

    public string $filepath;
    private $handle = null;
    private string $separator;


    public function __construct(string $filepath, string $separator=',')
    {
        $this->filepath = $filepath;
        $this->separator = $separator;
    }

    public function addLine(array $row): Self
    {
        if (empty($this->handle)) throw new RuntimeException('Must call open before adding a line');
        try{
            fputcsv($this->handle, $row);
        } catch (Throwable $e){
            $this->close();
            log_message('error',
                '{errorMsg} - {filepath}',
                [
                    'errorMsg' => $e->getMessage(),
                    'filepath' => $this->filepath
            ]);
        }
        return $this;
    }

    public function close(): void
    {
        if (!is_null($this->handle)){
            fclose($this->handle);
            $this->handle = null;
        }
    }

    public function open(): Self
    {
        $this->handle = fopen($this->filepath, 'w');
        return $this;
    }
}