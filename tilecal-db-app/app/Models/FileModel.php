<?php

namespace App\Models;

use CodeIgniter\Model;

class FileModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'files';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\File::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'mime', 'extension', 'filedata', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getBindedFilesInfo($id){
        $query = $this->select('file_id as id, name, description')
                      ->join('equipments_to_files', 'files.id = equipments_to_files.file_id')
                      ->where(sprintf("equipments_to_files.equipment_id = '%s'", $id));
        $query = $this->searchDeleted($query, false);
        $result = $query->findAll();
        return $result;
    }

    public function getFile($id=null, $columns=null, $searchDeleted=false)
    {
        if (is_null($columns)){
            $columns = 'id, name, description, created_at, updated_at, deleted_at';
        }
        $query = $this->select($columns);
        
        if (is_null($id)){
            $query = $this->searchDeleted($query, $searchDeleted);
            $result = $query->findAll();
        }
        else{
            $result = $query->withDeleted()->find($id);
        }

        return $result;
    }

    protected function searchDeleted($query, $searchDeleted){
        if ($searchDeleted){
            return $query->withDeleted()->where("files.deleted_at IS NOT NULL");
        }
        return $query;
    }
}
