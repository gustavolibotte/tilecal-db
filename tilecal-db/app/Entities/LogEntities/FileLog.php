<?php

namespace App\Entities\LogEntities;
use App\Entities\OperationLog;

class FileLog extends OperationLog
{
    protected array $msgFields = [
        'name',
        'description'
    ];
}
