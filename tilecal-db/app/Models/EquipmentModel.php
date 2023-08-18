<?php

namespace App\Models;

use App\Entities\OperationLog;
use App\Entities\Equipment;
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
        $changes = $this->getFullChanges($data);
        unset_array_keys($changes, $this->insertChangesIgnore);
        $operationLog = new OperationLog([
            'record_id' => $id,
            'table' => $this->table,
            'changes' => $changes,
            'action' => 'insert',
            'created_at' => $opTimestamp
        ]);
        return $operationLog;
    }

    protected function getFullChanges(Equipment $equipment): array
    {
        $changes = $equipment->toArray();
        $modelParams = remove_value('model_params', $changes);
        foreach ($modelParams as $key=>$value){
            $changes[$key] = $value;
        }
        $model = (new \App\Models\ModelModel())->find($changes['model_id']);
        $changes['model'] = $model->label;
        $location = (new \App\Models\LocationModel())->find($changes['location_id']);
        $changes['location'] = $location->name;
        $status = (new \App\Models\StatusModel())->find($changes['status_id']);
        $changes['status'] = $status->name;
        return $changes;
    }

    protected function getUpdateLog(Time $opTimestamp, int|string $id, Entity $new, Entity $old): OperationLog|array
    {
        $new = $new->toArray();
        $old = $old->toArray();
        unset_array_keys($old, $this->updateChangesIgnore);
        unset_array_keys($new, $this->updateChangesIgnore);
        if (array_key_exists('model_params', $new)){
            $oldModelParams = remove_value('model_params', $old);
            $newModelParams = remove_value('model_params', $new);
            $changes = $this->getChangedFields($old, $new);
            $modelParamsChanges = $this->getChangedFields($oldModelParams, $newModelParams);
            if (!empty($modelParamsChanges)){
                $changes = array_merge($changes, $modelParamsChanges);
            }
        }
        if (array_key_exists('model_id', $changes)){
            $model = (new \App\Models\ModelModel())->find($changes['model_id']);
            $changes['model'] = $model->label;
        }
        if (array_key_exists('location_id', $changes)){
            $location = (new \App\Models\LocationModel())->find($changes['location_id']);
            $changes['location'] = $location->name;
        }
        if (array_key_exists('status_id', $changes)){
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

    public function deleteWithLog($id=null, bool $purge = false, ?Time $opTimestamp = null)
    {
        if (is_null($opTimestamp)){
            $opTimestamp = Time::now();
        }
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->deleteWithLog($sampleId, $purge, $opTimestamp);
            }
            return $res;
        }
        $histModel = new HistModel($this->isMigration);
        $data = $this->find($id);
        $data->deleted_at = $opTimestamp;
        $operationLog = $this->getDeleteLog($opTimestamp, $id);
        $equipments2FilesModel = new Equipment2FileModel();
        $relations = $equipments2FilesModel->select('id')->where('equipment_id', $id)->findAll();
        $this->db->transStart();
        $res = $this->delete($id, $purge);
        foreach($relations as $relation){
            $relationLog = $equipments2FilesModel->getDeleteLog($opTimestamp, $relation->id);
            $equipments2FilesModel->delete($relation->id);
            $histModel->insert($relationLog);
        }
        $histModel->insert($operationLog);
        $this->db->transComplete();
        return $res;
    }

    protected function getDeleteLog(Time $opTimestamp, int|string $id): OperationLog|array
    {
        $equipment = $this->find($id);
        $changes = $this->getFullChanges($equipment);
        unset_array_keys($changes, $this->deleteChangesIgnore);
        $operationLog = new OperationLog([
            'record_id' => $id,
            'table' => $this->table,
            'changes' => $changes,
            'action' => 'delete',
            'created_at' => $opTimestamp
        ]);
        return $operationLog;
    }

    public function restoreWithLog(int|string|array $id, ?Time $opTimestamp = null): bool
    {
        if (is_null($opTimestamp)){
            $opTimestamp = Time::now();
        }
        if (is_array($id)){
            foreach($id as $sampleId){
                $res[] = $this->restoreWithLog($sampleId, $opTimestamp);
            }
            return $res;
        }
        $histModel = new HistModel($this->isMigration);
        $data = $this->onlyDeleted()->find($id);
        if (is_null($data)){
            throw new DataException('Called restore on a non deleted row.');
        }
        $operationLog = $this->getRestoreLog($opTimestamp, $id, $data);

        $equipments2FilesModel = new Equipment2FileModel();
        $relations = $equipments2FilesModel->withDeleted()->select('equipments_to_files.id')
                                           ->join('files', 'equipments_to_files.file_id = files.id')
                                           ->where('files.deleted_at IS NULL')
                                           ->where('equipment_id', $id)
                                           ->where('equipments_to_files.deleted_at', $data->deleted_at)
                                           ->findAll();
        $this->db->transStart();
        $this->restore($id, $opTimestamp);
        foreach($relations as $relation){
            $equipments2FilesModel->restoreWithLog($relation->id, $opTimestamp);
        }
        $histModel->insert($operationLog);
        $this->db->transComplete();

        return true;
    }

    protected function getRestoreIgnore(Time $opTimestamp, int|string $id, Entity $data): OperationLog|array
    {
        $changes = $this->getFullChanges($data);
        unset_array_keys($changes, $this->restoreChangesIgnore);
        $operationLog = new OperationLog([
            'record_id' => $id,
            'table' => $this->table,
            'changes' => $changes,
            'action' => 'restore',
            'created_at' => $opTimestamp
        ]);
        return $operationLog;
    }
}
