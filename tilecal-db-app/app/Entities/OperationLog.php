<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class OperationLog extends Entity
{
    protected $datamap = [
        'createdAt' => 'created_at',
        'recordId' => 'record_id',
    ];
    protected $dates   = ['created_at'];
    protected $casts   = [
         "changes" => "json",
         "record_id" => "integer"
    ];

    protected function getMessage(){
        if ($this->isInsert()){
            return $this->insertMessage();
        }elseif ($this->isUpdate()){
            return $this->updateMessage();
        }elseif ($this->isDelete()){
            return $this->deleteMessage();
        }else {
            return null;
        }
    }
    public function isInsert(){
        return ($this->action == 'insert');
    }
    public function isUpdate(){
        return ($this->action == 'update');
    }

    public function isDelete(){
        return ($this->action == 'delete');
    }

    protected function insertMessage(){
        $msg = sprintf('<b>%s</b> - ', $this->createdAt);
        $msg .= sprintf('<b>%s</b> created entry with ID %s with values:', $this->user, $this->recordId);
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            $msg .= sprintf('<li> <b>%s</b> - %s </li>', $name, $value);
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function updateMessage(){
        $msg = sprintf('<b>%s</b> - ', $this->createdAt);
        if (is_null($this->changes)){
            $msg .= sprintf('<b>%s</b> submited update with no changes', $this->user,);
            return $msg;
        }
        $msg .= sprintf('<b>%s</b> updated entry with ID <b>%s</b> with values:', $this->user, $this->recordId);
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            $msg .= sprintf('<li> <b>%s</b> - %s to %s </li>', $name, $value->old, $value->new);
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function deleteMessage(){
        $msg = '<p class=text-danger>';
        $msg .= sprintf('<b>%s</b> - ', $this->createdAt);
        $msg .= sprintf('<b>%s</b> deleted entry with ID <b>%s</b>', $this->user, $this->recordID);
        $msg .= '</p>';
        return $msg;
    }
}
