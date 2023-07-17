<?php

namespace App\Entities;

class Equipment2FileLog extends OperationLog{
    protected function insertMessage(){
        $msg = $this->getMessageStart();
        $msg .= sprintf('<b>%s</b> linked file %s to equipment %s',
                        $this->user, $this->changes->file_id, $this->changes->equipment_id);
        return $msg;
    }

    protected function updateMessage(){
        $msg = $this->getMessageStart();
        if (is_null($this->changes)){
            $msg .= sprintf('<b>%s</b> submited update with no changes', $this->user,);
            return $msg;
        }
        $msg .= sprintf('<b>%s</b> updated file link with values:', $this->user);
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            $msg .= sprintf('<li> <b>%s</b> - %s to %s </li>', $name, $value->old, $value->new);
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function deleteMessage(){
        $msg = $this->getMessageStart();
        $msg .= sprintf('<b>%s</b> removed link from file %s to equipment %s ',
                        $this->changes->file_id, $this->changes->equipment_id);
        return $msg;
    }
}
