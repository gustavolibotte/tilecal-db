<?php

namespace App\Models;

use App\Entities\OperationLog;
use App\Entities\Equipment;
use App\Entities\Equipment2Equipment;


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
            ->join('institutes', 'equipments.owner_id = institutes.id', 'left');
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
     *
     * @param string $id The equipment id to get relations from.
     * 
     * @return array All Equipment instances found
     */ 
    public function getRelatedEquipments(string $id): array
    {
        // $parentId = $this->getParentId($id);
        // $extraSelect = "CASE ";
        // $extraSelect .= sprintf(
        //     "WHEN equipments_to_equipments.equipment_b_id = %s AND equipments_to_equipments.type = %s THEN %s ",
        //     $this->db->escape($parentId),
        //     $this->db->escape('child'),
        //     $this->db->escape('sibling')
        // );
        // $extraSelect .= "ELSE equipments_to_equipments.type END AS relation_type";
        $extraSelect = "equipments_to_equipments.type AS relation_type";
        // Gets the equipments that have a relation that refers to this equipment
        $whereQuery = sprintf(
            "((equipments_to_equipments.equipment_b_id = %s AND equipments_to_equipments.equipment_a_id != %s)",
            $this->db->escape($id),
            $this->db->escape($id)
        );
        // Gets this id parent childs
        // $whereQuery .= sprintf(
        //     "(equipments_to_equipments.equipment_b_id = %s AND equipments_to_equipments.equipment_a_id != %s AND equipments_to_equipments.type = %s))",
        //     $this->db->escape($parentId),
        //     $this->db->escape($id),
        //     $this->db->escape('child')
        // );
        $query = $this->fullQuery($extraSelect)
            ->join('equipments_to_equipments', 'equipments.id = equipments_to_equipments.equipment_a_id', 'left')
            ->where('equipments_to_equipments.equipment_b_id', $id)
            ->where('equipments_to_equipments.equipment_a_id !=', $id);
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
        if (!($data instanceof Equipment)){
            throw new \TypeError('Equipment Model insert supports only Equipment as $data');
        }
        $data = $data->toRawArray();
        if(array_key_exists('parent_id', $data)){
            d($data);
            $parentId = remove_value('parent_id', $data);
            $slotId = remove_value('slot_id', $data);
        }
        $childId = parent::insert($data, true);
        if(isset($parentId) && isset($slotId)){
            $this->addParent($childId, $parentId, $slotId);
        }
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
        if(array_key_exists('parent_id', $data)){
            $parentId = remove_value('parent_id', $data);
            $slotId = remove_value('slot_id', $data);
        }
        $res = parent::update($id, $data);
        if(isset($parentId)){
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
        parent::delete($id, false);
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
        $parentChildModel = model('parentChildModel');
        $relations = $parentChildModel
            ->orWhere('parent_equip_id', $id)
            ->orWhere('child_equip_id', $id)
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

}
