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
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'model_id',
        'owner_id',
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
    protected $selectSideTable = '';

    public function initialize(){
        parent::initialize();
        $this->selectSideTable .= 'equipments.id, ';
        $this->selectSideTable .= 'models.label as model_label ';
        $this->insertLogIgnore[] = 'model_params';
        $this->updateLogIgnore[] = 'model_params';
        $this->deleteLogIgnore[] = 'model_params';
    }

    public function fullQuery(){
        $selectColumns = 'equipments.id, ';
        $selectColumns .= 'models.label as model_label, ';
        $selectColumns .= 'models.name as model_name, ';
        $selectColumns .= 'equipments.location_id as location_id, ';
        $selectColumns .= 'locations.name as location, ';
        $selectColumns .= 'locations.description as location_description, ';
        $selectColumns .= 'equipments.status_id as status_id, ';
        $selectColumns .= 'statuses.name as status, ';
        $selectColumns .= 'equipments.owner_id as owner_id, ';
        $selectColumns .= 'institutes.name as owner_name, ';
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
                      ->join('models parent_models', 'parents.model_id = parent_models.id', 'left')
                      ->join('institutes', 'equipments.owner_id = institutes.id');
        return $query;

    }

    public function getEquipment($id=null, $limit=null){
        $query = $this->fullQuery();
        if (is_null($id)){
            $result = ($limit)? $query->findAll($limit): $query->findAll();
        }
        else{
            $result = $query->find($id);
        }
        return $result;
    }

    public function getFileRelated($fileId, $limit=null){
        $query = $this->fullQuery()
                      ->join('equipments_to_files', 'equipments.id = equipments_to_files.equipment_id')
                      ->where(sprintf('equipments_to_files.file_id = %s', $fileId));
        $result = is_null($limit)? $query->findAll(): $query->findAll($limit);
        return $result;
    }

    public function getRelatedEquipments($equipmentId){
        $relatedEquipments = $this->getEquipment(limit:30);
        return $relatedEquipments;
    }

    protected function getUpdateLog($updateInfo){
        $id = $updateInfo["id"][0];
        $oldRow = $this->find($id)->toArray();
        if (array_key_exists("model_params", $updateInfo["data"])){ //Should always happen
            // Makes stdClass to assosciative array
            $oldModelParams = json_decode(json_encode($oldRow["model_params"]), true);
            $newModelParams = json_decode($updateInfo["data"]["model_params"], true);
            $changes = $this->getUpdateChanges($oldModelParams, $newModelParams);
        } else{
            $changes = [];
        }
        $changes = array_merge($changes,
                               $this->getUpdateChanges($oldRow, $updateInfo["data"]));
        
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

    protected function getDeleteLog($deleteLog){
        return $this->getInsertLog($deleteLog);
    }

    public function delete($id=null, bool $purge = false)
    {
        // The trans methods gauarantee that all database operations are made on the same transaction
        // This is important since we are writing in 2 tables to handle many to many relationships
        $equipments2FilesModel = new Equipment2FileModel();
        $relations = $equipments2FilesModel->select('id')->where('equipment_id', $id)->findAll();
        array_walk($relations, function(&$value, $key){ $value=$value->id;});
        $this->db->transStart();
        if (!empty($relations)){
            $equipments2FilesModel->delete($relations, $purge);
        }
        $res =  parent::delete($id, $purge);
        $this->db->transComplete();
        return $res;
    }

    public function restore($equipmentId): bool
    {
        // The trans methods gauarantee that all database operations are made on the same transaction
        // This is important since we are writing in 2 tables to handle many to many relationships
        d($equipmentId);
        $equipment = $this->onlyDeleted()->select('deleted_at')->find($equipmentId);
        d($equipment);
        $equipments2FilesModel = new Equipment2FileModel();
        // Gets the relations that were deleted at the same time as equipment deletion
        // from files that are not deleted
        $relations = $equipments2FilesModel->withDeleted()
                                           ->select('equipments_to_files.id')
                                           ->join('files', 'equipments_to_files.file_id = files.id')
                                           ->where('files.deleted_at IS NULL')
                                           ->where('equipment_id', $equipmentId)
                                           ->where('equipments_to_files.deleted_at', $equipment->deleted_at)
                                           ->findAll();
        d($relations);
        array_walk($relations, function(&$value, $key){ $value=$value->id;});
        d($relations);
        $this->db->transStart();
        if (!empty($relations)){
            $equipments2FilesModel->onlyDeleted()->update($relations, ['deleted_at'=>null]);
        }
        // $res = $this->withDeleted()->update($equipmentId, ['deleted_at' => null]);
        $res = parent::restore($equipmentId);
        $this->db->transComplete();
        return $res;
    }
}
