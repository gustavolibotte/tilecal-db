<?php

namespace App\Entities;

class File extends BaseEntity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        "id" => "integer",
    ];

    protected function getMime(): string
    {
        return mime_content_type($this->path);
    }

    protected function getFileObj(): \CodeIgniter\Files\File
    {
        return new \CodeIgniter\Files\File($this->path);
    }
}
