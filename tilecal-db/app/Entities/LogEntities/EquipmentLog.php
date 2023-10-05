<?php

namespace App\Entities\LogEntities;
use App\Entities\OperationLog;

class EquipmentLog extends OperationLog
{
    protected array $msgFields = [
        'id',
        'status',
        'location',
        'type',
        'owner',
        'serial',
        'type_params',
        'comments'
    ];
    protected $casts   = [
        'type_params' => 'json-array'
    ];

    protected function makeMsgList(): string
    {
        $msg = '<ul>';
        foreach ($this->msgFields as $fieldName){
            if (is_array($this->$fieldName)){
                foreach($this->$fieldName as $name=>$value){
                    $msg .= $this->makeListItem($name, $value);
                }
            } else{
                $msg .= $this->makeListItem($fieldName, $this->$fieldName);
            }
        }
        $msg .= '</ul>';
        return $msg;
    }
}
