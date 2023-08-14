<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class OperationLog extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at'];
    protected $casts   = [
         "changes" => "json-array"
    ];

    protected $msgIgnore = ['filedata'];
    
    protected function getMessage(){
        helper('string');
        $msgBuilder = sprintf(
            '%s%sMessage',
            $this->action, snake2CamelCase($this->table)
        );
        if (method_exists($this, $msgBuilder)){
            return $this->$msgBuilder();
        } else{
            $defaultMsgBuilder = sprintf(
                'default%sMessage',
                ucfirst($this->action)
            );
            return $this->$defaultMsgBuilder();
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

    protected function makeDanger($msg){
        $msg = '<p class=text-danger>' . $msg . '</p>';
        return $msg;
    }

    protected function getMessageStart(){
        $msg = sprintf('<b>%s</b> - <b>%s</b> - ', $this->created_at, $this->user);
        return $msg;
    }

    protected function insertEquipmentsToFilesMessage(){
        $msg = $this->getMessageStart();
        $fileLink = sprintf(
            '<a type="button" class="link-primary" href="%s">file</a>',
            base_url(sprintf('files/view/%s', $this->changes['file_id']))
        );
        $equipmentLink = sprintf(
            '<a type="button" class="link-primary" href="%s">equipment</a>',
            base_url(sprintf('equipments/view/%s', $this->changes['equipment_id']))
        );
        $msg .= sprintf('Link between %s and %s', $fileLink, $equipmentLink);
        return $msg;
    }

    protected function deleteEquipmentsToFilesMessage(){
        $msg = $this->getMessageStart();
        $fileLink = sprintf(
            '<a type="button" class="link-primary" href="%s">file</a>',
            base_url(sprintf('files/view/%s', $this->changes['file_id']))
        );
        $equipmentLink = sprintf(
            '<a type="button" class="link-primary" href="%s">equipment</a>',
            base_url(sprintf('equipments/view/%s', $this->changes['equipment_id']))
        );
        $msg .= sprintf('Link between %s and %s removed', $fileLink, $equipmentLink);
        return $msg;
    }

    protected function insertEquipmentsMessage(){
        $msg = $this->getMessageStart();
        $msg .= 'Entry created with values:';
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            if (!str_ends_with($name, '_id') and !in_array($name, $this->msgIgnore)){
                $msg .= sprintf('<li> <b>%s</b> - %s </li>', snake2Capitalized($name), $value);
            }
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function updateEquipmentsMessage(){
        $msg = $this->getMessageStart();
        if (is_null($this->changes) or (count($this->changes) == 0)){
            $msg .= 'Submited update with no changes';
            return $msg;
        }
        $msg .= 'Entry updated with values:';
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            if (!str_ends_with($name, '_id') and !in_array($name, $this->msgIgnore)){
                $msg .= sprintf('<li> <b>%s</b> - %s to %s </li>', snake2Capitalized($name), $value['old'], $value['new']);
            }
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function defaultInsertMessage(){
        $msg = $this->getMessageStart();
        $msg .= 'Entry created with values:';
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            if (!in_array($name, $this->msgIgnore)){
                $msg .= sprintf('<li> <b>%s</b> - %s </li>', snake2Capitalized($name), $value);
            }
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function defaultUpdateMessage(){
        $msg = $this->getMessageStart();
        if (is_null($this->changes)){
            $msg .= 'submited update with no changes';
            return $msg;
        }
        $msg .= 'Entry updated with values:';
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            if (!in_array($name, $this->msgIgnore)){
                $msg .= sprintf('<li> <b>%s</b> - %s to %s </li>', snake2Capitalized($name), $value['old'], $value['new']);
            }
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function defaultDeleteMessage(){
        $msg = $this->getMessageStart();
        $msg .= 'Entry deleted with values:';
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            if (!in_array($name, $this->msgIgnore)){
                $msg .= sprintf('<li> <b>%s</b> - %s </li>', $name, $value);
            }
        }
        $msg .= '</ul>';
        return $msg;
    }

    static function orderByCreation($a, $b){
        return $a->created_at <=> $b->created_at;
    }

    static function orderByCreationReverse($a, $b){
        return $b->created_at <=> $a->created_at;
    }
}
