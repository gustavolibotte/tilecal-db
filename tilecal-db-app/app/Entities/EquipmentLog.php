<?php

namespace App\Entities;

class EquipmentLog extends OperationLog{

    protected function insertMessage(){
        $msg = $this->getMessageStart();
        $msg .= sprintf('<b>%s</b> created equipment with ID %s with values:', $this->user, $this->recordId);
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            $msg .= sprintf('<li> <b>%s</b> - %s </li>', $name, $value);
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function updateMessage(){
        $msg = $this->getMessageStart();
        if (is_null($this->changes)){
            $msg .= sprintf('<b>%s</b> equipment update with no changes', $this->user,);
            return $msg;
        }
        $msg .= sprintf('<b>%s</b> updated equipment with ID <b>%s</b> with values:', $this->user, $this->recordId);
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            $msg .= sprintf('<li> <b>%s</b> - %s to %s </li>', $name, $value->old, $value->new);
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function deleteMessage(){
        $msg = $this->getMessageStart();
        $msg .= sprintf('<b>%s</b> deleted equipment with ID <b>%s</b>', $this->user, $this->recordID);
        return $msg;
    }
}
