<?php

namespace App\Entities;

class FileLog extends OperationLog
{
    public $messageFieldIgnore = ['mime'];

    protected function insertMessage(){
        $msg = $this->getMessageStart();
        $msg .= sprintf('<b>%s</b> created file with ID %s and values:', $this->user, $this->recordId);
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            if (! in_array($name, $this->messageFieldIgnore)){
                $msg .= sprintf('<li> <b>%s</b> - %s </li>', $name, $value);
            }
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function updateMessage(){
        $msg = $this->getMessageStart();
        if (is_null($this->changes)){
            $msg .= sprintf('<b>%s</b> file update with no changes', $this->user,);
            return $msg;
        }
        $msg .= sprintf('<b>%s</b> updated file with ID <b>%s</b> and values:', $this->user, $this->recordId);
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            $msg .= sprintf('<li> <b>%s</b> - %s to %s </li>', $name, $value->old, $value->new);
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function deleteMessage(){
        $msg = $this->getMessageStart();
        $msg .= sprintf('<b>%s</b> deleted file with ID <b>%s</b>', $this->user, $this->recordID);
        return $msg;
    }
}
