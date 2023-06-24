<?php

namespace App\Models;

use CodeIgniter\Model;

class EquipmentModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'equipments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'model_id', 'location', 'status', 'parent_id'];

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
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function equipmentsWithModelName()
    {   
        $columns = 'equipments.id, ';
        $columns .= 'models.name as model, ';
        $columns .= 'equipments.location, ';
        $columns .= 'equipments.status, ';
        $columns .= 'equipments.parent_id, ';
        $columns .= 'equipments.created_at, ';
        $columns .= 'equipments.updated_at';
        $result = $this->select($columns)
                      ->join('models', 'equipments.model_id = models.id')
                      ->orderBy('equipments.updated_at', 'DESC')
                      ->findAll();
        return $result;
    }

    public function fetchTableData()
    {
        $columns = 'equipments.id, ';
        $columns .= 'models.name as model, ';
        $columns .= 'equipments.location, ';
        $columns .= 'equipments.status, ';
        $columns .= 'equipments.parent_id, ';
        $columns .= 'equipments.created_at, ';
        $columns .= 'equipments.updated_at';
        $result = $this->select($columns)
                      ->join('models', 'equipments.model_id = models.id')
                      ->orderBy('equipments.updated_at', 'DESC')
                      ->findAll();
        echo json_encode($result);
    }
}
