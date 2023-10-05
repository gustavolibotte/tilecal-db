<?php

namespace App\Entities\LogEntities;
use App\Entities\OperationLog;

class EquipFileRelationLog extends OperationLog
{
    protected function insertMessage(): string
    {
        $msg = sprintf('Links %s and %s',
            $this->makeFileLink($this->file_id, $this->file_name),
            $this->makeEquipmentLink($this->equipment_id)
        );
        return $msg;
    }

    protected function deleteMessage(): string
    {   
        $msg = sprintf('Unlinks %s and %s',
            $this->makeFileLink($this->file_id, $this->file_name),
            $this->makeEquipmentLink($this->equipment_id)
        );
        return $msg;
    }
}
