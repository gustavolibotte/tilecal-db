<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\ModelModel;

class EquipmentModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'equipments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Equipment::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $generalFields    = ['id', 'model_id', 'location', 'status', 'parent_id', 'comments', 'serial'];
    protected $utilityFields    = ['model'];
    protected $pmtFields        = ['beta', 'nominal_hv', 'qe', 'type'];
    protected $allowedFields    = ['id', 'model_id', 'location', 'status', 'parent_id', 'comments', 'serial', 'model_params'];

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
    protected $beforeInsert   = ['addModelID', 'makeJSON'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
    protected $baseJoinedColumns = null;

    public function initialize(){
        $this->allowedFields = array_merge($this->allowedFields, $this->utilityFields);
        $this->allowedFields = array_merge($this->allowedFields, $this->pmtFields);
        $this->baseJoinedColumns = 'equipments.id, ';
        $this->baseJoinedColumns .= 'models.formatted_name as model, ';
        $this->baseJoinedColumns .= 'equipments.location, ';
        $this->baseJoinedColumns .= 'equipments.status, ';
        $this->baseJoinedColumns .= 'equipments.parent_id, ';
        $this->baseJoinedColumns .= 'equipments.serial, ';
        $this->baseJoinedColumns .= 'equipments.comments, ';
        $this->baseJoinedColumns .= 'equipments.model_params, ';
        $this->baseJoinedColumns .= 'equipments.updated_at, ';
        $this->baseJoinedColumns .= 'equipments.created_at ';
    }

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
        if (is_null($id)){
            $result = $this->select($this->baseJoinedColumns)
                           ->join('models', 'equipments.model_id = models.id')
                           ->orderBy('equipments.updated_at', 'DESC')
                           ->findAll();
        }
        else{
            $result = $this->select($this->baseJoinedColumns)
                           ->where(sprintf("equipments.id = '%s'", $id))
                           ->join('models', 'equipments.model_id = models.id')
                           ->orderBy('equipments.updated_at', 'DESC')
                           ->first();
        }

        return $result;
    }

    public function getSideTable(){

        $columns = 'equipments.id, ';
        $columns .= 'models.formatted_name as model ';
        $result = $this->select($columns)
                        ->join('models', 'equipments.model_id = models.id')
                        ->orderBy('equipments.updated_at', 'DESC')
                        ->findAll();
        return $result;
    }
    /**
     * Returns all the equipments for a specific model name
     *
     * @param string $modelName Model's name to query for equipments
     *
     * @return array The query results
     */ 
    public function getEquipmentsWhereModel($modelName){

        $result = $this->select($this->baseJoinedColumns)
                       ->where(sprintf("models.name = '%s'", $modelName))
                       ->join('models', 'equipments.model_id = models.id')
                       ->orderBy('equipments.updated_at', 'DESC')
                       ->findAll();
        return $result;
    }

    protected function makeJSON($data){
        $extraData = null;
        foreach ($data['data'] as $field=>$value){
            if (! in_array($field, $this->generalFields)){
                $extraData[$field] = $value;
                unset($data['data'][$field]);
            }
        }
        if (! is_null($extraData)){
            $data['data']['model_params'] = json_encode($extraData);
        }
        return $data;
    }

    protected function addModelID($data){
        if (! array_key_exists('model_id', $data['data'])){
            $modelName = $data['data']['model'];
            unset($data['data']['model']);
            $modelModel = new ModelModel();
            $modelData = $modelModel->select('id')->where(sprintf("name = '%s'", $modelName))->first();
            $data['data']['model_id'] = $modelData['id'];
        }
        return $data;
    }
}
