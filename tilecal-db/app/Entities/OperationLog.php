<?php

namespace App\Entities;

class OperationLog extends BaseEntity
{
    protected $datamap = [];
    protected $dates   = ['created_at'];
    protected $casts   = [
         "changes" => "json-array"
    ];

    protected array $msgIgnore = [
        'files' => [
            'mime',
            'path',
            'id'
        ],
        'equipments' => [
            'location_id',
            'status_id',
            'type_id',
            'owner_id'
        ],
        'parent_child_equips' => ['id'],
        'equipments_to_files' => ['id']
    ];
    
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

    protected function getFileLink(): string
    {
        $fileLink = sprintf(
            '<a type="button" class="link-primary" href="%s">%s</a>',
            base_url(sprintf('files/view/%s', $this->changes['file_id'])),
            $this->changes['file_name']
        );
        return $fileLink;
    }

    protected function getEquipmentLink(string $idField): string
    {
        $equipmentLink = sprintf(
            '<a type="button" class="link-primary" href="%s">%s</a>',
            base_url(sprintf('equipments/view/%s', $this->changes[$idField])),
            $this->changes[$idField]
        );
        return $equipmentLink;
    }

    protected function insertEquipmentsToFilesMessage(){
        $msg = sprintf('Link between %s and %s created',
            $this->getFileLink(),
            $this->getEquipmentLink('equipment_id')
        );
        return $msg;
    }

    protected function deleteEquipmentsToFilesMessage(){
        $msg = sprintf('Link between %s and %s removed',
            $this->getFileLink(),
            $this->getEquipmentLink('equipment_id')
        );
        return $msg;
    }

    protected function restoreEquipmentsToFilesMessage(){
        $msg = sprintf('Link between %s and %s restored',
            $this->getFileLink(),
            $this->getEquipmentLink('equipment_id')
        );
        return $msg;
    }

    protected function insertParentChildEquipsMessage(): string
    {
        $msg = sprintf('Makes %s parent to %s',
            $this->getEquipmentLink('parent_equip_id'),
            $this->getEquipmentLink('child_equip_id')
        );
        return $msg;
    }

    protected function deleteParentChildEquipsMessage(): string
    {
        $msg = sprintf('Removes %s parent to %s',
        $this->getEquipmentLink('parent_equip_id'),
        $this->getEquipmentLink('child_equip_id')
        );
        return $msg;
    }

    protected function defaultInsertMessage(){
        $msg = 'Entry created with values:';
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            if (!in_array($name, $this->msgIgnore[$this->table])){
                $msg .= sprintf('<li> <b>%s</b> - %s </li>', snake2Capitalized($name), $value);
            }
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function defaultUpdateMessage(){
        $msg = '';
        if (is_null($this->changes)){
            $msg .= 'submited update with no changes';
            return $msg;
        }
        $msg .= 'Entry updated with values:';
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            if (!in_array($name, $this->msgIgnore[$this->table])){
                $msg .= sprintf('<li> <b>%s</b> - %s</li>', snake2Capitalized($name), $value);
            }
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function defaultDeleteMessage(){
        $msg = 'Entry deleted with values:';
        $msg .= '<ul>';
        foreach ($this->changes as $name=>$value){
            if (!in_array($name, $this->msgIgnore[$this->table])){
                $msg .= sprintf('<li> <b>%s</b> - %s </li>', $name, $value);
            }
        }
        $msg .= '</ul>';
        return $msg;
    }

    protected function defaultRestoreMessage(){
        $msg = 'Restored this deleted entry';
        return $msg;
    }

    static function orderByCreation($a, $b){
        return $a->created_at <=> $b->created_at;
    }

    static function orderByCreationReverse($a, $b){
        return $b->created_at <=> $a->created_at;
    }

    protected function printEquipmentField(string $fieldName): bool
    {
        $printId = ($fieldName == 'parent_id') || (!str_ends_with($fieldName, '_id'));
        return $printId;
    }
}
