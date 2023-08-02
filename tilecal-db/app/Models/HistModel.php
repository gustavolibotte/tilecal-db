<?php

namespace App\Models;

class HistModel extends BaseModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'hist';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\OperationLog::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['table', 'action', 'record_id', 'changes', 'filedata'];

    // Dates
    protected $useTimestamps = false;
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
    protected $beforeInsert   = ['addUser'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function initialize()
    {
        helper('authentication');
    }

    public function addUser($operationInfo){
        $operationInfo['data']['user'] = ($this->isMigration)? 'root': getUser()->username;
        return $operationInfo;
    }
}
