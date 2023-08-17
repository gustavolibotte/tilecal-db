<?php

namespace App\Models;

use App\Entities\OperationLog;

class FileModel extends LogModel
{
    protected $table            = 'files';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\File::class;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'mime', 'filedata', 'description'];

    public function initialize(){
        parent::initialize();
        $this->insertChangesIgnore[] = 'filedata';
        $this->updateChangesIgnore[] = 'filedata';
        $this->deleteChangesIgnore[] = 'filedata';
    }

    public function getEquipmentRelated($id){
        $query = $this->select('files.id, files.name, files.description, files.created_at, files.deleted_at')
                      ->join('equipments_to_files', 'files.id = equipments_to_files.file_id')
                      ->where(sprintf("equipments_to_files.equipment_id = '%s'", $id));
        $result = $query->findAll();
        return $result;
    }

    public function getFile($id=null)
    {
        $query = $this->select('id, name, mime, description, created_at, updated_at, deleted_at');
        if (is_null($id)){
            $result = $query->findAll();
        }
        else{
            $result = $query->find($id);
        }

        return $result;
    }
}
