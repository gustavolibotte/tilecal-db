<?php

namespace App\Entities;

class OperationLog extends BaseEntity
{
    protected $datamap = [];
    protected $dates   = ['created_at'];
    protected $casts   = [
         "changes" => "json-array"
    ];

    protected array $msgFields = [];
    
    protected function getMessage(): string
    {
        helper('string');

        $msgBuilder = sprintf(
            '%sMessage',
            $this->action
        );
        $msg = $this->$msgBuilder();
        if ($this->action == 'danger'){
            $msg = $this->makeDanger($msg);
        }
        return $msg;
    }

    protected function makeListItem(string $fieldName, mixed $fieldValue): string
    {
        $msg = sprintf(
            '<li> <b>%s</b> - %s </li>',
            snake2Capitalized($fieldName),
            $fieldValue
        );
        return $msg;
    }

    protected function makeMsgList(): string
    {
        $msg = '<ul>';
        foreach ($this->msgFields as $fieldName){
            $msg .= $this->makeListItem($fieldName, $this->$fieldName);
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function insertMessage(): string
    {
        $msg = 'Entry created:';
        $msg .= $this->makeMsgList();
        return $msg;
    }

    protected function updateMessage(): string
    {
        $msg = 'Entry updated:';
        $msg .= $this->makeMsgList();
        return $msg;
    }

    protected function deleteMessage()
    {
        $msg = 'Entry deleted:';
        $msg .= $this->makeMsgList();
        return $msg;
    }

    protected function restoreMessage()
    {
        $msg = 'Entry restored:';
        $msg .= $this->makeMsgList();
        return $msg;
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

    public function isRestore(){
        return ($this->action == 'restore');
    }

    protected function makeDanger($msg){
        $msg = '<p class=text-danger>' . $msg . '</p>';
        return $msg;
    }

    protected function getMessageStart(){
        $msg = sprintf('<b>%s</b> - <b>%s</b> - ', $this->created_at, $this->user);
        return $msg;
    }

    protected function makeFileLink(int $id, ?string $name=null): string
    {
        $text = empty($name)? $id: $name;
        $fileLink = sprintf(
            '<a type="button" class="link-primary" href="%s">%s</a>',
            url_to('file_view', $id),
            $text
        );
        return $fileLink;
    }

    protected function makeEquipmentLink(string $id): string
    {
        $equipmentLink = sprintf(
            '<a type="button" class="link-primary" href="%s">%s</a>',
            url_to('equipment_view', $id),
            $id
        );
        return $equipmentLink;
    }
}
