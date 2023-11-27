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
}
