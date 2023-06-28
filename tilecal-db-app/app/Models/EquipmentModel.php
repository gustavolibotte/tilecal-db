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
    protected $allowedFields    = ['id', 'model_id', 'location', 'status', 'parent_id', 'comments'];

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

    public function getEquipmentRaw($id=null, $columns='*'){

        if (is_null($id)){
            $result = $this->findAll();
        }
        else{
            $result = $this->select($columns)
                           ->where(sprintf("id = '%s'", $id))
                           ->first();
        }
        return $result;
    }

    public function getEquipment($id=null)
    {   
        $columns = 'equipments.id, ';
        $columns .= 'models.name as model, ';
        $columns .= 'equipments.location, ';
        $columns .= 'equipments.status, ';
        $columns .= 'equipments.parent_id, ';
        $columns .= 'equipments.comments, ';
        if (is_null($id)){
            $result = $this->select($columns)
                           ->join('models', 'equipments.model_id = models.id')
                           ->orderBy('equipments.last_update', 'DESC')
                           ->findAll();
        }
        else{
            $result = $this->select($columns)
                           ->where(sprintf("equipments.id = '%s'", $id))
                           ->join('models', 'equipments.model_id = models.id')
                           ->orderBy('equipments.last_update', 'DESC')
                           ->first();
        }

        return $result;
    }

    public function getSideTable(){

        $columns = 'equipments.id, ';
        $columns .= 'models.name as model ';
        $result = $this->select($columns)
                        ->join('models', 'equipments.model_id = models.id')
                        ->orderBy('equipments.last_update', 'DESC')
                        ->findAll();
        return $result;
    }
    /**
     * Returns all the equipments for a specific model name
     *
     * @param string $modelName Model's name to query for equipments
     *
     * @return result The query results
     */ 
    public function getEquipmentsWhereModel($modelName){
        $columns = 'equipments.id, ';
        $columns .= 'equipments.location, ';
        $columns .= 'equipments.status, ';
        $columns .= 'equipments.parent_id, ';
        $columns .= 'equipments.comments, ';
        $result = $this->select($columns)
                       ->where(sprintf("models.name = '%s'", $modelName))
                       ->join('models', 'equipments.model_id = models.id')
                       ->orderBy('equipments.last_update', 'DESC')
                       ->findAll();
        return $result;
    }
}
