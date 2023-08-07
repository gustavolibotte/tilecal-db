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
    protected $allowedFields    = [
        'id',
        'model_id',
        'location_id',
        'status_id',
        'parent_id',
        'comments',
        'serial',
        'model_params'
    ];

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
    protected $selectSideTable;

    // Custom
    protected $insertLogIgnore = ['created_at', 'updated_at', 'deleted_at', 'model_params'];
    protected $updateLogIgnore = ['created_at', 'updated_at', 'deleted_at', 'model_params'];

    public function initialize(){
        $this->selectSideTable = 'equipments.id, ';
        $this->selectSideTable .= 'models.label as model_label ';

        parent::initialize();
    }

    protected function fullQuery(){
        $selectColumns = 'equipments.id, ';
        $selectColumns .= 'models.label as model_label, ';
        $selectColumns .= 'models.name as model_name, ';
        $selectColumns .= 'locations.name as location, ';
        $selectColumns .= 'locations.description as location_description, ';
        $selectColumns .= 'statuses.name as status, ';
        $selectColumns .= 'parent_models.label as parent_model_label, ';
        $selectColumns .= 'parent_models.name as parent_model_name, ';
        $selectColumns .= 'equipments.parent_id, ';
        $selectColumns .= 'equipments.serial, ';
        $selectColumns .= 'equipments.comments, ';
        $selectColumns .= 'equipments.model_params, ';
        $selectColumns .= 'equipments.updated_at, ';
        $selectColumns .= 'equipments.created_at, ';
        $selectColumns .= 'equipments.deleted_at ';
        $query = $this->select($selectColumns)
                      ->join('models', 'equipments.model_id = models.id', 'left')
                      ->join('locations', 'equipments.location_id = locations.id', 'left')
                      ->join('statuses', 'equipments.status_id = statuses.id', 'left')
                      ->join('equipments parents', 'equipments.parent_id = parents.id', 'left')
                      ->join('models parent_models', 'parents.model_id = parent_models.id', 'left');
        return $query;

    }

    public function getEquipment($id=null, $searchDeleted=false, $limit=null){
        $query = $this->fullQuery();
        if (is_null($id)){
            $query = $this->searchDeleted($query, $searchDeleted);
            $result = ($limit)? $query->findAll($limit): $query->findAll();
        }
        else{
            $result = $query->withDeleted()->find($id);
        }
        return $result;
    }

    public function getFileRelated($fileId, $limit=null){
        $selectColumns = 'equipments.id, ';
        $selectColumns .= 'models.label as model_label, ';
        $selectColumns .= 'models.name as model_name, ';
        $selectColumns .= 'locations.name as location, ';
        $selectColumns .= 'locations.description as location_description, ';
        $selectColumns .= 'statuses.name as status, ';
        $selectColumns .= 'equipments.parent_id, ';
        $selectColumns .= 'equipments.serial, ';
        $selectColumns .= 'equipments.comments, ';
        $selectColumns .= 'equipments.model_params, ';
        $selectColumns .= 'equipments.updated_at, ';
        $selectColumns .= 'equipments.created_at, ';
        $selectColumns .= 'equipments.deleted_at ';
        $query = $this->select($selectColumns)
                      ->join('models', 'equipments.model_id = models.id')
                      ->join('locations', 'equipments.location_id = locations.id')
                      ->join('statuses', 'equipments.status_id = statuses.id')
                      ->join('equipments_to_files', 'equipments.id = equipments_to_files.equipment_id')
                      ->where(sprintf('equipments_to_files.file_id = %s', $fileId));
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
        $query = $this->fullQuery();
        $query = $this->searchDeleted($query, $searchDeleted);
        $result = $query->findAll();
        return $result;
    }

    protected function searchDeleted($query, $searchDeleted){
        if ($searchDeleted){
            return $query->withDeleted()->where("equipments.deleted_at IS NOT NULL");
        }
        return $query;
    }

    protected function getUpdateLog($updateInfo){
        $id = $updateInfo["id"][0];
        $oldRow = $this->find($id)->toArray();
        $changes = $this->getUpdateChanges($oldRow, $updateInfo["data"]);

        // if (array_key_exists("model_params", $updateInfo["data"])){
        //     $oldModelParams = json_decode($oldRow["model_params"], true);
        //     $newModelParams = json_decode($updateInfo["data"]["model_params"], true);
        //     $changes = array_merge($changes,
        //                            $this->getUpdateChanges($oldModelParams, $newModelParams));
        // }
        foreach ($changes as $name=>$change){
            if ($name == 'model_id'){
                $changes['model'] = $this->updateSubsIds('label', new ModelModel(), $change);
            } elseif ($name == 'location_id'){
                $changes['location'] = $this->updateSubsIds('name', new LocationModel(), $change);
            } elseif ($name == 'status_id'){
                $changes['status'] = $this->updateSubsIds('name', new StatusModel(), $change);
            }
        }
        $updateLog = new OperationLog([
            "record_id" => $id,
            "action" => "update",
            "changes" => $changes
        ]);
        return $updateLog;
    }

    protected function updateSubsIds(string $key, BaseModel $model, array $change){
        $entities = $model->getIdArray([$change['old'], $change['new']]);
        $newChange = [
            'old' => $entities[$change['old']]->toArray()[$key],
            'new' => $entities[$change['new']]->toArray()[$key],
        ];
        return $newChange;
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
        $model = (new \App\Models\ModelModel())->find($insertInfo['data']['model_id']);
        $changes['model'] = $model->label;
        $location = (new \App\Models\LocationModel())->find($insertInfo['data']['location_id']);
        $changes['location'] = $location->name;
        $status = (new \App\Models\StatusModel())->find($insertInfo['data']['status_id']);
        $changes['status'] = $status->name;
        $insertLog = new OperationLog([
            "record_id" => $insertInfo["data"]["id"],
            "action" => "insert",
            "changes" => $changes
        ]);

        return $insertLog;
    }
}
