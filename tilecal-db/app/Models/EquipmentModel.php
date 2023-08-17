<?php

namespace App\Models;

use App\Entities\OperationLog;
use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;
use CodeIgniter\Database\Exceptions\DataException;


class EquipmentModel extends LogModel
{
    protected $table            = 'equipments';
    protected $returnType       = \App\Entities\Equipment::class;
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

    // Custom
    protected $selectSideTable = '';

    public function initialize(){
        parent::initialize();
        $this->selectSideTable .= 'equipments.id, ';
        $this->selectSideTable .= 'models.label as model_label ';
        $this->insertChangesIgnore[] = 'model_params';
        $this->updateChangesIgnore[] = 'model_params';
        $this->deleteChangesIgnore[] = 'model_params';
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

    protected function getInsertLog(Time $opTimestamp, int|string $id, Entity $data): OperationLog|array
    {
        $operationLog = parent::getInsertLog($opTimestamp, $id, $data);
        $changes = $operationLog->changes;
        foreach ($data->model_params as $key=>$value){
            $hanges[$key] = $value;
        }
        $model = (new \App\Models\ModelModel())->find($changes['model_id']);
        $changes['model'] = $model->label;
        $location = (new \App\Models\LocationModel())->find($changes['location_id']);
        $changes['location'] = $location->name;
        $status = (new \App\Models\StatusModel())->find($changes['status_id']);
        $changes['status'] = $status->name;
        $operationLog->changes = $changes;
        return $operationLog;
    }

    protected function getUpdateLog(Time $opTimestamp, int|string $id, Entity $data): OperationLog|array
    {
        $new = $data->toArray();
        $old = $this->find($id)->toArray();
        $new = unset_array_keys($new, $this->updateChangesIgnore);
        $oldModelParams = remove_value('model_params', $old);
        $newModelParams = remove_value('model_params', $new);
        $changes = $this->getChangedFields($old, $new);
        $modelParamsChanges = $this->getChangedFields($oldModelParams, $newModelParams);
        if (!empty($modelParamsChanges)){
            $changes = array_merge($changes, $modelParamsChanges);
        }
        $changesKeys = array_keys($changes);
        if (in_array('model_id', $changesKeys)){
            $model = (new \App\Models\ModelModel())->find($changes['model_id']);
            $changes['model'] = $model->label;
        }
        if (in_array('location_id', $changesKeys)){
            $location = (new \App\Models\LocationModel())->find($changes['location_id']);
            $changes['location'] = $location->name;
        }
        if (in_array('status_id', $changesKeys)){
            $status = (new \App\Models\StatusModel())->find($changes['status_id']);
            $changes['status'] = $status->name;
        }

        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $id,
            'action' =>'update',
            'table' => $this->table,
            'created_at' => $opTimestamp
        ]);
        return $operationLog;
    }

    public function deleteWithLog($id=null, bool $purge = false)
    {
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->delete($sampleId, $purge);
            }
            return $res;
        }
        $histModel = new HistModel($this->isMigration);
        $opTimestamp = Time::now();
        $data = $this->find($id);
        $data->deleted_at = $opTimestamp;
        $operationLog = $this->getDeleteLog($opTimestamp, $id);
        $equipments2FilesModel = new Equipment2FileModel();
        $relations = $equipments2FilesModel->select('id')->where('equipment_id', $id)->findAll();
        $relationTable = $this->builder($equipments2FilesModel->table);
        $this->db->transStart();
        $res = $this->delete($id, $purge);
        foreach($relations as $relation){
            $relationTable->where('id', $relation->id)->set('deleted_at', $opTimestamp)->update();
            $relationLog = $equipments2FilesModel->getDeleteLog($opTimestamp, $relation->id);
            $histModel->insert($relationLog);
        }
        // $equipments2FilesModel->delete($relations, $purge);
        $histModel->insert($operationLog);
        $this->db->transComplete();
        return $res;
    }

    protected function getDeleteLog(Time $opTimestamp, int|string $id): OperationLog|array
    {
        $operationLog = parent::getDeleteLog($opTimestamp, $id);
        $modelParams = remove_value('model_params', $operationLog->changes);
        foreach ($modelParams as $key=>$value){
            $changes[$key] = $value;
        }
        $model = (new \App\Models\ModelModel())->find($operationLog->changes['model_id']);
        $operationLog->changes['model'] = $model->label;
        $location = (new \App\Models\LocationModel())->find($operationLog->changes['location_id']);
        $operationLog->changes['location'] = $location->name;
        $status = (new \App\Models\StatusModel())->find($operationLog->changes['status_id']);
        $operationLog->changes['status'] = $status->name;
        return $operationLog;
    }

    public function restoreWithData(int|string|array $id): bool
    {
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->restore($sampleId);
            }
            return $res;
        }
        $histModel = new HistModel($this->isMigration);
        $opTimestamp = Time::now();
        $data = $this->onlyDeleted()->find($id);
        if (is_null($data)){
            throw new DataException('Called restore on a non deleted row.');
        }
        $operationLog = $this->getRestoreLog($opTimestamp, $id, $data);
        $equipment = $this->onlyDeleted()->select('deleted_at')->find($id);

        $equipments2FilesModel = new Equipment2FileModel();
        $relations = $equipments2FilesModel->withDeleted()
                                           ->select('equipments_to_files.id')
                                           ->join('files', 'equipments_to_files.file_id = files.id')
                                           ->where('files.deleted_at IS NULL')
                                           ->where('equipment_id', $id)
                                           ->where('equipments_to_files.deleted_at', $equipment->deleted_at)
                                           ->findAll();
        $relationsQueryBuilder = $this->builder($equipments2FilesModel->table);
        array_walk($relations, function(&$value, $key){ $value=$value->id;});
        $this->db->transStart();
        $this->withDeleted()->update($id, ['deleted_at' => null]);
        $histModel->insert($operationLog);
        $this->db->transComplete();

        return true;
    }
}
