<?php

namespace App\Models;

use App\Entities\ParentChildRelation;
use App\Entities\OperationLog;
use App\Entities\Equipment;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;


class EquipmentModel extends LogModel
{
    protected $table            = 'equipments';
    protected $returnType       = Equipment::class;
    protected $allowedFields    = [
        'id',
        'type_id',
        'type_name',
        'owner_id',
        'owner_mne',
        'location_id',
        'location_name',
        'status_id',
        'status_name',
        'parent_id',
        'slot_id',
        'serial',
        'comments',
        'type_params'
    ];

    // Custom
    protected $selectSideTable = '';

    public function initialize(){
        parent::initialize();
        helper(['app_array']);
        $this->selectSideTable .= 'equipments.id, ';
        $this->selectSideTable .= 'equipment_types.label as type_label ';
        $this->afterDelete[] = 'deleteRelations';
        $this->beforeInsert = array_merge(
            ['substituteFKs'],
            $this->beforeInsert
        );
        $this->beforeUpdate = array_merge(
            ['substituteFKs'],
            $this->beforeUpdate
        );
    }

    public function minQuery(BaseBuilder|Model $queryBuilder=null): BaseBuilder|Model
    {
        if (is_null($queryBuilder)){
            $queryBuilder = $this;
        }
        $queryBuilder->select('equipments.id as id');
        $queryBuilder->select('equipment_types.label as type_label');
        $queryBuilder->join('equipment_types', 'equipments.type_id = equipment_types.id', 'left');
        return $queryBuilder;
    }

    public function fullQuery(BaseBuilder|Model $queryBuilder=null): BaseBuilder|Model
    {
        if (is_null($queryBuilder)){
            $queryBuilder = $this;
        }
        $queryBuilder->select('equipments.id as id');
        $queryBuilder->select('equipment_types.label as type_label');
        $queryBuilder->select('equipment_types.name as type_name');
        $queryBuilder->select('equipments.location_id as location_id');
        $queryBuilder->select('locations.name as location');
        $queryBuilder->select('locations.description as location_description');
        $queryBuilder->select('equipments.status_id as status_id');
        $queryBuilder->select('statuses.name as status');
        $queryBuilder->select('equipments.owner_id as owner_id');
        $queryBuilder->select('institutes.name as owner_name');
        $queryBuilder->select('institutes.mne as owner_mne');
        $queryBuilder->select('equipment_types.project as type_project');
        $queryBuilder->select('equipment_types.system as type_system');
        $queryBuilder->select('equipments.serial');
        $queryBuilder->select('equipments.comments');
        $queryBuilder->select('equipments.type_params');
        $queryBuilder->select('equipments.updated_at');
        $queryBuilder->select('equipments.created_at');
        $queryBuilder->select('equipments.deleted_at');

        $queryBuilder = $queryBuilder
            ->join('equipment_types', 'equipments.type_id = equipment_types.id', 'left')
            ->join('locations', 'equipments.location_id = locations.id', 'left')
            ->join('statuses', 'equipments.status_id = statuses.id', 'left')
            ->join('institutes', 'equipments.owner_id = institutes.id', 'left');

        return $queryBuilder;
    }

    public function fullQueryRelated(BaseBuilder|Model $queryBuilder=null): BaseBuilder|Model
    {
        if (is_null($queryBuilder)){
            $queryBuilder = $this;
        }
        $queryBuilder->fullQuery();

        return $queryBuilder;
    }

    public function joinFiles(BaseBuilder|Model $queryBuilder=null): BaseBuilder|Model
    {
        if (is_null($queryBuilder)){
            $queryBuilder = $this;
        }
        $queryBuilder = $queryBuilder
            ->join('equipments_to_files', 'equipments.id = equipments_to_files.equipment_id', 'left');
        
        return $queryBuilder;
    }

    public function getEquipment($id=null)
    {
        $query = $this->fullQuery();
        $result = empty($id)? $query->findAll():  $query->find($id);
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
     *
     * @param string $id The equipment id to get relations from.
     * 
     * @return array All Equipment instances found
     */ 
    public function getRelatedEquipments(string|array $id): array
    {   
        $parentExtra = sprintf(
            "%s as relation_type, ts.name as slot_name",
            $this->db->escape('parent'));
        $whereClause = is_array($id)? 'whereIn': 'where';
        $parentQuery = $this->fullQuery(db_connect()->table('equipments'))
            ->select($parentExtra)
            ->join('parent_child_equips pce', 'equipments.id=pce.parent_equip_id', 'left')
            ->join('type_slots ts', 'pce.slot_id=ts.id', 'left')
            ->$whereClause('pce.child_equip_id', $id);

        $extraQuery = sprintf(
            "%s as relation_type, ts.name as slot_name",
            $this->db->escape('child'));
        $equipments = $this->fullQuery()->select($extraQuery)
            ->join('parent_child_equips pce', 'equipments.id=pce.child_equip_id', 'left')
            ->join('type_slots ts', 'pce.slot_id=ts.id', 'left')
            ->$whereClause('pce.parent_equip_id', $id)
            ->union($parentQuery)
            ->findAll();
        return $equipments;
    }


    /**
     * Returns the parent id from a specific equipment id.
     * Considers that the equipment has only one parent
     * 
     * @param string $id The equipment id to get the parent from.
     * 
     * @return ?ParentChildRelation $parentRelation The obtained parent relation
     */ 
    public function getParentRelation(string $id): ?ParentChildRelation
    {
        $parentRelation = model('ParentChildModel')
            ->where('child_equip_id', $id)
            ->first();
        if (is_null($parentRelation)){
            return null;
        } else {
            return $parentRelation;
        }
    }

    /**
     * Inserts equipment data.
     * The equipment entity can also have a parent_id field that it will be inserted correctly
     *
     * @param Equipment $data Equipment entity
     * 
     * @throws TypeError If data is not instance of EquipmenturnId is true
     * @return ?string returns id if $returnID is true
     */
    public function insert($data = null, bool $returnID = true)
    {
        $this->db->transBegin();
        if (!($data instanceof Equipment)){
            throw new \TypeError('Equipment Model insert supports only Equipment as $data');
        }
        $data = $data->toRawArray();
        $parentId = remove_value('parent_id', $data, false);
        $slotId = remove_value('slot_id', $data, false);
        $childId = parent::insert($data, true);
        if(isset($parentId) && isset($slotId)){
            $this->addParent($childId, $parentId, $slotId);
        }
        $this->db->transCommit();
        if($returnID){
            return $childId;
        }
    }

    protected function getInsertLog(array $data): OperationLog
    {
        $data['data'] = $this->mergeTypeParams($data['data']);
        unset_array_keys($data['data'], $this->insertChangesIgnore);
        $data['data'] = $this->addForeignLabels($data['data']);
        $operationLog = new OperationLog([
            'record_id' => $data['id'],
            'table' => $this->table,
            'changes' => $data['data'],
            'action' => 'insert'
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
    public function addParent(string $childId, string $parentId, int $slotId): bool
    {
        $parentChildModel = model('ParentChildModel');
        $parentChildModel->insert([
            'parent_equip_id' => $parentId,
            'child_equip_id' => $childId,
            'slot_id' => $slotId
        ]);
        return true;
    }

    /**
     * Updates equipment data.
     * The equipment entity can also have a parent_id field that it will be updated correctly
     *
     * @param mixed $id Ids of the entries to update
     * @param Equipment $data Equipment entity
     * 
     * @throws TypeError If data is not instance of Equipment 
     * @return bool
     */
    public function update($id = null, $data = null): bool
    {
        if (!($data instanceof Equipment)){
            throw new \TypeError('Equipment Model insert supports only Equipment as $data');
        }
        if (is_null($id)){
            throw new \TypeError('$id must be passed');
        }
        $data = $data->toRawArray();
        if(array_key_exists('parent_id', $data) || array_key_exists('slot_id', $data)){
            $parentId = remove_value('parent_id', $data);
            $slotId = remove_value('slot_id', $data);
        }
        $res = parent::update($id, $data);
        if(isset($parentId) && isset($slotId)){
            $this->deleteParent($id);
            $this->addParent($id, $parentId, $slotId);
        }
        return $res;
    }

    protected function getUpdateLog(array $data): OperationLog
    {
        $old = $this->find($data['id'][0]);
        $new = $this->mergeTypeParams($data['data']);
        $old = $this->mergeTypeParams($old->toRawArray());
        unset_array_keys($old, $this->updateChangesIgnore);
        unset_array_keys($new, $this->updateChangesIgnore);
        $changes = $this->getChangedFields($old, $new);
        $changes = $this->addForeignLabels($changes);

        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $data['id'][0],
            'action' =>'update',
            'table' => $this->table
        ]);
        return $operationLog;
    }

    public function delete($id = null, bool $purge = false)
    {
        $this->db->transBegin();
        parent::delete($id, false);
        $this->db->transCommit();
    }

    protected function getDeleteLog(array $data): OperationLog
    {
        $changes = $this->find($data['id'][0])->toRawArray();
        $changes = $this->mergeTypeParams($changes);
        unset_array_keys($changes, $this->deleteChangesIgnore);
        $changes = $this->addForeignLabels($changes);

        $operationLog = new OperationLog([
            'changes' => $changes,
            'record_id' => $data['id'][0],
            'action' =>'delete',
            'table' => $this->table
        ]);
        return $operationLog;
    }

    protected function deleteRelations(array $data): array
    {   
        $this->deleteFileRelations($data['id']);
        $this->deleteEquipmentRelations($data['id']);
        return $data;
    }

    public function deleteFileRelations(string|array $id): bool
    {
        $equipments2FilesModel = model('Equipment2FileModel');
        $whereClause = (is_array($id))? 'whereIn': 'where';
        $relations = $equipments2FilesModel
            ->select('id')
            ->$whereClause('equipment_id', $id)
            ->findAll();
        foreach($relations as $relation){
             $equipments2FilesModel->delete($relation->id, true);
        }
        return true;
    }

    public function deleteEquipmentRelations(string|array $id): bool
    {
        $whereClause = (is_array($id))? 'orWhereIn': 'orWhere';
        $parentChildModel = model('ParentChildModel');
        $relations = $parentChildModel
            ->$whereClause('parent_equip_id', $id)
            ->$whereClause('child_equip_id', $id)
            ->findAll();
        foreach($relations as $relation){
             $parentChildModel->delete($relation->id, true);
        }
        return true;
    }

    /**
     * Removes parent relation between child and parent.
     * Assumes that conditions were validated before
     *
     * @param string $childId Child equipment id
     * 
     * @return bool Always true
     */ 
    public function deleteParent(string $childId): bool
    {   
        $parentChildModel = model('ParentChildModel');
        $relations = $parentChildModel->where('child_equip_id', $childId)->findAll();
        foreach($relations as $relation){
            $parentChildModel->delete($relation->id, purge: true);
        }
        return true;
    }

    protected function mergeTypeParams(array $data): array
    {
        if(!array_key_exists('type_params', $data)){
            return $data;
        }
        $typeParams = json_decode(remove_value('type_params', $data), true);
        foreach ($typeParams as $key=>$value){
            $data[$key] = $value;
        }
        return $data;
    }

    protected function unmergeTypeParams(array $data): array
    {
        $data['data']['type_params'] = json_encode(remove_value('type_params', $data));
        return $data;
    }

    protected function addForeignLabels(array $changes): array
    {
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
        if (array_key_exists('owner_id', $changes)){
            $institute = (new \App\Models\InstituteModel())->find($changes['owner_id']);
            $changes['owner'] = $institute->name;
        }
        return $changes;
    }

    /**
     * Updates the builder to select child equipments
     * Joins the desired tables to select only equipments child to a given $parentId
     *
     * @param string $parentId  Equipment Id for the parent id to search for childs
     * @param mixed $typeId
     * If string selects childs where equipment_types.id = $typeId
     * If array selectes chuilds where equipment_types.id in $typeId
     * @param ?array $idIgnore Ignores the equipment ids inside this array
     * 
     * @return Self This model instance
     */
    public function queryChilds(string $parentId, mixed $typeId=null, ?array $idIgnore=null): Self
    {
        $this->join('equipments_to_equipments', 'equipments.id = equipments_to_equipments.equipment_b_id', 'left')
            ->join('equipment_types', 'equipments.type_id = equipment_types.id', 'left')
            ->where('equipments_to_equipments.equipment_a_id', $parentId)
            ->where('equipments_to_equipments.type', 'parent');
        if ((!empty($typeId)) && ($typeId instanceof string)){
            $this->where('equipment_types.id', $typeId);
        } elseif ((!empty($typeId)) && (is_array($typeId))){
            $this->whereIn('equipment_types.id', $typeId);
        }
        if (!empty($idIgnore)){
            $this->whereNotIn('equipments.id', $idIgnore);
        }
        $this->withDeletedJoin(['equipments_to_equipments']);
        return $this;
    }

    /**
     * Updates the builder to add soft delete condition to joined tables
     *
     * @param array $joinedTables Array with the joined table names

     * @return Self This model instance
     */
    public function withDeletedJoin(array $joinedTables): Self
    {
        if ($this->tempUseSoftDeletes){
            foreach ($joinedTables as $tableName){
                $this->where(sprintf(
                    '`%s`.`%s` IS NULL',
                    $tableName,
                    $this->deletedField
                ));
            }
        }
        return $this;
    }

    protected function substituteFKs(array $data): array
    {
        if (array_key_exists('type_name', $data['data'])){
            $data['data']['type_id'] = $this->getFKId(
                remove_value('type_name', $data['data']),
                'name', 'EquipmentTypeModel'
            );
        }
        if (array_key_exists('owner_mne', $data['data'])){
            $data['data']['owner_id'] = $this->getFKId(
                remove_value('owner_mne', $data['data']),
                'mne', 'InstituteModel'
            );
        }
        if (array_key_exists('location_name', $data['data'])){
            $data['data']['location_id'] = $this->getFKId(
                remove_value('location_name', $data['data']),
                'name', 'LocationModel'
            );
        }
        if (array_key_exists('status_name', $data['data'])){
            $data['data']['status_id'] = $this->getFKId(
                remove_value('status_name', $data['data']),
                'name', 'StatusModel'
            );
        }
        return $data;
    }

    protected function getFKId(mixed $value, string $col, string $modelName): int|string
    {
        return model($modelName)
            ->select('id')
            ->where($col, $value)
            ->first()->id;
    }

    /**
     * Returns true if the equipment has a specific slot available for a specific type
     *
     * @param string $id  Id of the equipment to check for avaiable slot
     * @param string $slotId Id of the slot to check
     * @param ?string $childId Child Id to disconsider
     * 
     * @return bool True if the equipment slot is available for a given equipment type
     */
    public function hasAvailableSlot(string $id, string $slotId, ?string $childId=null): bool
    {
        $equipHasSlot = $this->equipHasSlot($id, $slotId);
        if (!$equipHasSlot){
            return false;
        }
        $slotEmpty = $this->equipSlotIsEmpty($id, $slotId);
        return $slotEmpty;
    }

    /**
     * Returns true if equipment has a specified slot Id
     *
     * @param string $id  Id of the equipment to check for slot
     * @param string $slotId Id of the slot to check
     * 
     * @return bool true if equipment has the specified slot
     */
    public function equipHasSlot(string $id, string $slotId): bool
    {
        $equipHasSlot = $this->select('1')
            ->join('equipment_types et', 'equipments.type_id = et.id', 'left')
            ->join('type_slots ts', 'ts.parent_type_id=et.id', 'left')
            ->where('ts.id', $slotId)
            ->find($id);
        return !empty($equipHasSlot);
    }

    /**
     * Returns true if the equipment slot is empty
     * Doesn't check if the equipment can actually have the slot
     *
     * @param string $id  Id of the equipment to check for slot
     * @param string $slotId Id of the slot to check
     * @param ?string $childId Child Id to disconsider
     * 
     * @return bool true if equipment has the specified slot
     */
    protected function equipSlotIsEmpty(string $id, string $slotId, ?string $childId=null): bool
    {
        $builder = $this->builder('parent_child_equips pce')
            ->select('1')
            ->join('type_slots ts', 'pce.slot_id=ts.id', 'left')
            ->where('pce.parent_equip_id', $id)
            ->where('ts.id', $slotId);
        if(!empty($childId)){
            $builder = $builder->where('pce.child_equip_id !=', $childId);
        }
            $res = $builder->get()->getResult();
        return empty($res);
    }

}
