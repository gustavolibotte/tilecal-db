<?php

namespace App\Models;

use App\Entities\OperationLog;
use App\Entities\Equipment;
use App\Entities\Equipment2Equipment;
use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;
use CodeIgniter\Database\Exceptions\DataException;


class EquipmentModel extends LogModel
{
    protected $table            = 'equipments';
    protected $returnType       = \App\Entities\Equipment::class;
    protected $allowedFields    = [
        'id',
        'type_id',
        'owner_id',
        'location_id',
        'status_id',
        'parent_id',
        'serial',
        'comments',
        'type_params'
    ];

    // Custom
    protected $selectSideTable = '';

    public function initialize(){
        parent::initialize();
        $this->selectSideTable .= 'equipments.id, ';
        $this->selectSideTable .= 'equipment_types.label as type_label ';
        $this->insertWithLogCallbacks[] = 'insertParentId';
        $this->deleteWithLogCallbacks[] = 'deleteRelations';
        d($this->deleteWithLogCallbacks);
    }

    public function fullQuery(?string $extraSelect=null){
        $selectColumns = 'equipments.id as id, ';
        $selectColumns .= 'equipment_types.label as type_label, ';
        $selectColumns .= 'equipment_types.name as type_name, ';
        $selectColumns .= 'equipments.location_id as location_id, ';
        $selectColumns .= 'locations.name as location, ';
        $selectColumns .= 'locations.description as location_description, ';
        $selectColumns .= 'equipments.status_id as status_id, ';
        $selectColumns .= 'statuses.name as status, ';
        $selectColumns .= 'equipments.owner_id as owner_id, ';
        $selectColumns .= 'institutes.name as owner_name, ';
        $selectColumns .= 'statuses.name as status, ';
        $selectColumns .= 'equipments.serial, ';
        $selectColumns .= 'equipments.comments, ';
        $selectColumns .= 'equipments.type_params, ';
        $selectColumns .= 'equipments.updated_at, ';
        $selectColumns .= 'equipments.created_at, ';
        $selectColumns .= 'equipments.deleted_at ';
        if (!is_null($extraSelect)){
            $selectColumns .= sprintf(', %s', $extraSelect);
        }
        $query = $this->select($selectColumns)
                      ->join('equipment_types', 'equipments.type_id = equipment_types.id', 'left')
                      ->join('locations', 'equipments.location_id = locations.id', 'left')
                      ->join('statuses', 'equipments.status_id = statuses.id', 'left')
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

    /**
     * Returns an array with all the related equipments to a specific file given its id
     *
     * @param string|int $id file id to get related equipments from
     * 
     * @param array array with related equipment entities
     */
    public function getFileRelated(string|int $id): array
    {
        $query = $this->fullQuery()
                      ->join('equipments_to_files', 'equipments.id = equipments_to_files.equipment_id', 'left')
                      ->where('equipments_to_files.file_id', $id);
        if ($this->tempUseSoftDeletes){    // If withDeleted was called this evaluates to false
            $query = $query->where('equipments_to_files.deleted_at IS NULL');
        }
        $result = $query->findAll();
        return $result;
    }

    /**
     * Returns the related equipments for a given equipment id.
     * This function breaks if equipments_to_equipments have 2 rows with same a, b id pair
     *
     * @param string $id The equipment id to get relations from.
     * 
     * @return array All Equipment instances found
     */ 
    public function getRelatedEquipments(string $id): array
    {
        $parentId = $this->getParentId($id);
        $extraSelect = "CASE ";
        $extraSelect .= sprintf(
            "WHEN equipments_to_equipments.equipment_b_id = %s AND equipments_to_equipments.type = %s THEN %s ",
            $this->db->escape($parentId),
            $this->db->escape('child'),
            $this->db->escape('sibling')
        );
        $extraSelect .= "ELSE equipments_to_equipments.type END AS relation_type";
        // Gets the equipments that have a relation that refers to this equipment
        $whereQuery = sprintf(
            "((equipments_to_equipments.equipment_b_id = %s AND equipments_to_equipments.equipment_a_id != %s) OR ",
            $this->db->escape($id),
            $this->db->escape($id)
        );
        // Gets the this id parent childs
        $whereQuery .= sprintf(
            "(equipments_to_equipments.equipment_b_id = %s AND equipments_to_equipments.equipment_a_id != %s AND equipments_to_equipments.type = %s))",
            $this->db->escape($parentId),
            $this->db->escape($id),
            $this->db->escape('child')
        );
        $query = $this->fullQuery($extraSelect)
            ->join('equipments_to_equipments', 'equipments.id = equipments_to_equipments.equipment_a_id', 'left')
            ->where($whereQuery);
        if ($this->tempUseSoftDeletes){
            $query->where('equipments_to_equipments.deleted_at IS NULL');
        }
        $equipments = $query->findAll();
        return $equipments;
    }

    /**
     * Returns the parent id from a specific equipment id.
     * Considers that the equipment has only one parent
     * 
     * @param string $equipmentId The equipment id to get the parent from.
     * 
     * @return ?string The obtained parent id
     */ 
    public function getParentId(string $equipmentId): ?string
    {
        $parentRelation = $this->select('equipment_b_id as parent_id')
                               ->join('equipments_to_equipments', 'equipments.id = equipments_to_equipments.equipment_a_id')
                               ->where('equipments_to_equipments.equipment_a_id', $equipmentId)
                               ->where('equipments_to_equipments.type', 'child')
                               ->first();
        if (is_null($parentRelation)){
            return null;
        } else {
            return $parentRelation->parent_id;
        }
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
        helper('app_array');
        $changes = $equipment->toArray();
        $typeParams = remove_value('type_params', $changes);
        foreach ($typeParams as $key=>$value){
            $changes[$key] = $value;
        }
        $type = (new \App\Models\EquipmentTypeModel())->find($changes['type_id']);
        $changes['type'] = $type->label;
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
        if (array_key_exists('type_params', $new)){
            $oldModelParams = remove_value('type_params', $old);
            $newModelParams = remove_value('type_params', $new);
            $changes = $this->getChangedFields($old, $new);
            $typeParamsChanges = $this->getChangedFields($oldModelParams, $newModelParams);
            if (!empty($typeParamsChanges)){
                $changes = array_merge($changes, $typeParamsChanges);
            }
        }
        if (array_key_exists('type_id', $changes)){
            $type = (new \App\Models\EquipmentTypeModel())->find($changes['type_id']);
            $changes['type'] = $type->label;
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
        $histModel = new HistModel();
        // $data = $this->find($id);
        // $data->deleted_at = $opTimestamp;
        $operationLog = $this->getDeleteLog($opTimestamp, $id);
        // $relations = $equipments2FilesModel->select('id')->where('equipment_id', $id)->findAll();
        $res = $this->delete($id, $purge);
        // Relations are always hard deleted
        
        // foreach($relations as $relation){
        //     $equipments2FilesModel->deleteWithLog($relation->id, $purge, $opTimestamp, false);
        // }
        $histModel->insert($operationLog);
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
        $histModel = new HistModel();
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
        $this->restore($id, $opTimestamp);
        foreach($relations as $relation){
            $equipments2FilesModel->restoreWithLog($relation->id, $opTimestamp, false);
        }
        $histModel->insert($operationLog);
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

    /**
     * Adds parent relation between child and parent.
     * Assumes that conditions were validated before
     *
     * @param string $childId Child equipment id
     * @param string $parentId Parent equipment id
     * 
     * @return bool Always true
     */ 
    public function addParent(string $childId, string $parentId): bool
    {
        $equipment2EquipmentModel = new Equipments2EquipmentsModel();
        $childRelation = new Equipment2Equipment([
            'equipment_a_id' => $childId,
            'equipment_b_id' => $parentId,
            'type' => 'child'
        ]);
        $parentRelation = new Equipment2Equipment([
            'equipment_b_id' => $childId,
            'equipment_a_id' => $parentId,
            'type' => 'parent'
        ]);
        $opTimestamp = Time::now();
        $equipment2EquipmentModel->insertWithLog($parentRelation, opTimestamp: $opTimestamp);
        $equipment2EquipmentModel->insertWithLog($childRelation, opTimestamp: $opTimestamp);
        return true;
    }

    public function removeParent(string $childId): bool
    {   
        $parentId = $this->getParentId($childId);
        $equipment2EquipmentModel = new Equipments2EquipmentsModel();
        $ids = $equipment2EquipmentModel
            ->select('id')
            ->orWhere(sprintf(
                "equipment_a_id = %s AND equipment_b_id = %s",
                $this->db->escape($childId),
                $this->db->escape($parentId)
            ))
            ->orWhere(sprintf(
                "equipment_b_id = %s AND equipment_a_id = %s",
                $this->db->escape($childId),
                $this->db->escape($parentId)
            ))
            ->findAll();
        array_walk($ids, function($value, $key){
            $ids[$key] = $value->id;
        });
        $equipment2EquipmentModel->deleteWithLog($ids);
        return true;
    }

    protected function insertParentId(Time $opTimestamp, mixed $id, Entity $data, OperationLog $operationLog)
    {
        if (is_null($data->parent_id)){
            return null;
        }
        $childId = $data->id;
        $parentId = $data->parent_id;
        $equipment2EquipmentModel = new Equipments2EquipmentsModel();
        $childRelation = new Equipment2Equipment([
            'equipment_a_id' => $childId,
            'equipment_b_id' => $parentId,
            'type' => 'child'
        ]);
        $parentRelation = new Equipment2Equipment([
            'equipment_b_id' => $childId,
            'equipment_a_id' => $parentId,
            'type' => 'parent'
        ]);
        $opTimestamp = Time::now();
        $equipment2EquipmentModel->insertWithLog($parentRelation, opTimestamp: $opTimestamp);
        $equipment2EquipmentModel->insertWithLog($childRelation, opTimestamp: $opTimestamp);
        return true;
    }

    protected function deleteRelations(array &$data): array
    {
        $this->deleteFileRelations($data['id'], $data['opTimestamp']);
        $this->deleteEquipmentRelations($data['id'], $data['opTimestamp']);
        return $data;
    }

    protected function deleteFileRelations(string $id, Time $opTimestamp)
    {
        $equipments2FilesModel = new Equipment2FileModel();
        $relations = $equipments2FilesModel->select('id')->where('equipment_id', $id)->findAll();
        foreach($relations as $relation){
             $equipments2FilesModel->deleteWithLog($relation->id, true, $opTimestamp, false);
        }
    }

    public function deleteEquipmentRelations(string $id, Time $opTimestamp)
    {
        $equipments2Equipments = new Equipments2EquipmentsModel();
        $relations = $equipments2Equipments->select('id')->where('equipment_id', $id)->findAll();
        foreach($relations as $relation){
             $equipments2Equipments->deleteWithLog($relation->id, true, $opTimestamp, false);
        }
    }
}
