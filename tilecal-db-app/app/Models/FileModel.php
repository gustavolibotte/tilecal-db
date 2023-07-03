<?php

namespace App\Models;

use CodeIgniter\Model;

class FileModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'files';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'filetype', 'filedata', 'description'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';
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
        $result = $this->select('file_id as id, name, description')
                       ->join('equipments_to_files', 'files.id = equipments_to_files.file_id')
                       ->where(sprintf("equipments_to_files.equipment_id = '%s'", $id))
                       ->findAll();
        return $result;
    }

    public function getFile($id=null, $columns=null)
    {
        if (is_null($columns)){
            $columns = 'id, name, description';
        }
        
        if (is_null($id)){
            $result = $this->select($columns)
                           ->orderBy('last_update', 'DESC')
                           ->findAll();
        }
        else{
            $result = $this->select($columns)
                           ->where(sprintf("id = %s", $id))
                           ->first();
        }

        return $result;
    }
}
