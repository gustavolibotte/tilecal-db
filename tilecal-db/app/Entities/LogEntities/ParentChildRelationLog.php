<?php

namespace App\Entities\LogEntities;
use App\Entities\OperationLog;

class ParentChildRelationLog extends OperationLog
{
    protected $casts   = [
        'file_id' => 'integer'
    ];

    protected function insertMessage(): string
    {
        $msg = sprintf('Make %s parent to %s',
            $this->makeEquipmentLink($this->parent_equip_id),
            $this->makeEquipmentLink($this->child_equip_id)
        );
        return $msg;
    }

    protected function deleteMessage(): string
    {
        $msg = sprintf('Removes %s parent to %s',
            $this->makeEquipmentLink($this->parent_equip_id),
            $this->makeEquipmentLink($this->child_equip_id)
        );
        return $msg;
    }
}
