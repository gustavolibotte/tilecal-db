<?php

namespace App\Models;

use App\Entities\OperationLog;

class EquipmentModel extends LogModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'equipments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Equipment::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['barcode', 'model_id', 'location_id', 'status_id', 'parent_id', 'comments', 'serial', 'model_params'];

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
    protected $beforeInsert   = ['makeJSON', 'addModelID'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['makeJSON', 'addModelID'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
    protected $baseJoinedColumns = null;
    protected $selectSideTable = null;

    // Custom
    protected $generalFields    = ['created_at', 'updated_at', 'deleted_at'];
    protected $utilityFields    = ['model_name'];
    protected $pmtFields        = ['beta', 'nominal_hv', 'qe', 'type'];
    protected $insertLogIgnore = ['created_at', 'updated_at', 'deleted_at', 'model_params'];
    protected $updateLogIgnore = ['created_at', 'updated_at', 'deleted_at', 'model_params'];

    public function initialize(){
        
        $this->generalFields = array_merge($this->generalFields, $this->allowedFields, $this->utilityFields);
        $this->allowedFields = array_merge($this->allowedFields, $this->utilityFields, $this->pmtFields);

        $this->baseJoinedColumns = 'equipments.id, ';
        $this->baseJoinedColumns = 'equipments.barcode, ';
        $this->baseJoinedColumns .= 'models.label as model_label, ';
        $this->baseJoinedColumns .= 'models.name as model_name, ';
        $this->baseJoinedColumns .= 'locations.name as location, ';
        $this->baseJoinedColumns .= 'statuses.name as status, ';
        $this->baseJoinedColumns .= 'parent_models.label as parent_model_label, ';
        $this->baseJoinedColumns .= 'parent_models.name as parent_model_name, ';
        $this->baseJoinedColumns .= 'equipments.parent_id, ';
        $this->baseJoinedColumns .= 'equipments.serial, ';
        $this->baseJoinedColumns .= 'equipments.comments, ';
        $this->baseJoinedColumns .= 'equipments.model_params, ';
        $this->baseJoinedColumns .= 'equipments.updated_at, ';
        $this->baseJoinedColumns .= 'equipments.created_at, ';
        $this->baseJoinedColumns .= 'equipments.deleted_at ';

        $this->selectSideTable = 'equipments.id, ';
        $this->selectSideTable .= 'models.label as model_label ';

        parent::initialize();
    }

    public function getEquipment($id=null, $searchDeleted=false, $limit=null){
        $query = $this->select($this->baseJoinedColumns)
                      ->join('models', 'equipments.model_id = models.id')
                      ->join('locations', 'equipments.location_id = locations.id')
                      ->join('statuses', 'equipments.status_id = statuses.id')
                      ->join('equipments parents', 'equipments.parent_id = parents.id')
                      ->join('models parent_models', 'parents.model_id = parent_models.id');
        if (is_null($id)){
            $query = $this->searchDeleted($query, $searchDeleted);
            $result = ($limit)? $query->findAll($limit): $query->findAll();
        }
        else{
            $result = $query->withDeleted()->find($id);
        }
        return $result;
    }

    public function getFileRelatedEquipments($fileID, $limit=null){
        $query = $this->select($this->baseJoinedColumns)
                      ->join('models', 'equipments.model_id = models.id')
                      ->join('equipments_to_files', 'equipments.id = equipments_to_files.equipment_id')
                      ->where(sprintf('equipments_to_files.file_id = %s', $fileID));
        $result = is_null($limit)? $query->findAll(): $query->findAll($limit);
        return $result;
    }

    public function getSideTable($searchDeleted=false, $limit=null){
        $query = $this->select($this->selectSideTable)
                      ->join('models', 'equipments.model_id = models.id');
        $query = $this->searchDeleted($query, $searchDeleted);
        
        if (!is_null($limit)){
            $result = $query->findAll($limit);
        }
        else{
            $result = $query->findAll();
        }
        return $result;
    }

    public function getRelatedEquipments($equipmentId){
        $relatedEquipments = $this->getEquipment(limit:30);
        return $relatedEquipments;
    }
    /**
     * Returns all the equipments for a specific model name
     *
     * @param string $modelName Model's name to query for equipments
     *
     * @return array The query results
     */ 
    public function getEquipmentsWhereModel($modelName, $searchDeleted=false){
        $query = $this->select($this->baseJoinedColumns)
                      ->where(sprintf("models.name = '%s'", $modelName))
                      ->join('models', 'equipments.model_id = models.id')
                      ->join('locations', 'equipments.location_id = locations.id')
                      ->join('statuses', 'equipments.status_id = statuses.id')
                      ->join('equipments parents', 'equipments.parent_id = parents.id')
                      ->join('models parent_models', 'parents.model_id = parent_models.id');
        $query = $this->searchDeleted($query, $searchDeleted);
        $result = $query->findAll();
        return $result;
    }

    protected function makeJSON($data){
        if (array_key_exists('model_params', $data['data'])){
            return $data;
        }

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
            $modelName = $data['data']['model_name'];
            unset($data['data']['model_name']);
            $modelModel = new ModelModel();
            $modelData = $modelModel->select('id')->where(sprintf("name = '%s'", $modelName))->first();
            $data['data']['model_id'] = $modelData['id'];
        }
        return $data;
    }

    protected function searchDeleted($query, $searchDeleted){
        if ($searchDeleted){
            return $query->withDeleted()->where("equipments.deleted_at IS NOT NULL");
        }
        return $query;
    }

    protected function getUpdateLog($updateInfo){
        $id = $updateInfo["id"][0];
        $oldRow = $this->find($id)->toRawArray();
        $changes = $this->getUpdateChanges($oldRow, $updateInfo["data"]);

        if (array_key_exists("model_params", $updateInfo["data"])){
            $oldModelParams = json_decode($oldRow["model_params"]);
            $newModelParams = json_decode($updateInfo["data"]["model_params"]);
            $changes = array_merge($changes,
                                   $this->getUpdateChanges($oldModelParams, $newModelParams));
        }
        $updateLog = new OperationLog([
            "record_id" => $id,
            "action" => "update",
            "changes" => $changes
        ]);
        return $updateLog;
    }

    protected function getInsertLog($insertInfo){
        $changes = [];
        foreach ($insertInfo['data'] as $name=>$value){
            if (!in_array($name, $this->insertLogIgnore)){
                $changes[$name] = $value;
            }
        }
        $modelParams = json_decode($insertInfo["data"]["model_params"]);
        foreach ($modelParams as $name=>$value){
            if (!in_array($name, $this->insertLogIgnore)){
                $changes[$name] = $value;
            }
        }
        $insertLog = new OperationLog([
            "record_id" => $insertInfo["id"],
            "action" => "insert",
            "changes" => $changes
        ]);

        return $insertLog;
    }
}
