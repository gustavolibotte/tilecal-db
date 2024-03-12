<?php

namespace App\Models;

use App\Entities\ParentChildRelation;
use App\Entities\Equipment;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use TypeError;


class EquipmentModel extends LogModel
{
    protected $table            = 'equipments';
    protected $returnType       = Equipment::class;
    protected $allowedFields    = [
        'id',
        'type_id',
        'owner_id',
        'location_id',
        'status_id',
        'parent_id',
        'slot_id',
        'serial',
        'comments',
        'type_params',
        'detector_location_id'
    ];

    public function initialize(){
        parent::initialize();
        helper(['app_array']);
        $this->afterDelete[] = 'deleteRelations';
    }

    public function fullQuery(BaseBuilder|Model $queryBuilder=null): BaseBuilder|Model
    {
        if (is_null($queryBuilder)){
            $queryBuilder = $this;
        }
        $queryBuilder->select('equipments.id as id');
        $queryBuilder->select('equipment_types.name as type_name');
        $queryBuilder->select('locations.name as location_name');
        $queryBuilder->select('locations.description as location_description');
        $queryBuilder->select('statuses.name as status_name');
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
        $queryBuilder->select('detector_locations.name as detector_location_name');

        $queryBuilder = $queryBuilder
            ->join('equipment_types', 'equipments.type_id = equipment_types.id', 'left')
            ->join('locations', 'equipments.location_id = locations.id', 'left')
            ->join('statuses', 'equipments.status_id = statuses.id', 'left')
            ->join('institutes', 'equipments.owner_id = institutes.id', 'left')
            ->join('detector_locations', 'equipments.detector_location_id=detector_locations.id', 'left');

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
        $this->db->transException(true)->transBegin();
        if (!($data instanceof Equipment)){
            throw new TypeError('Equipment Model insert supports only Equipment as $data');
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
        $this->db->transException(true)->transBegin();
        if (!($data instanceof Equipment)){
            throw new TypeError('Equipment Model insert supports only Equipment as $data');
        }
        if (is_null($id)){
            throw new TypeError('Equipment id must be passed');
        }
        $data = $data->toRawArray();
        if(array_key_exists('parent_id', $data) || array_key_exists('slot_id', $data)){
            $parentId = remove_value('parent_id', $data);
            $slotId = remove_value('slot_id', $data);
        }
        $res = parent::update($id, $data);
        if (!empty($data['location_id'])){
            $equipment = $this->select('location_id')->find($id);
            $children = $this->getChildrenIDs($id);
            if (!empty($children)){
                $this->builder()->set('location_id', $equipment->location_id)
                ->whereIn('id', $children)
                ->update();
            }
        }
        if(isset($parentId) && isset($slotId)){
            $this->deleteParent($id);
            $this->addParent($id, $parentId, $slotId);
        }
        $this->db->transCommit();
        return $res;
    }

    public function delete($id = null, bool $purge = false)
    {
        $this->db->transException(true)->transBegin();
        parent::delete($id, false);
        $this->db->transCommit();
    }

    protected function deleteRelations(array $data): array
    {   
        $this->deleteFileRelations($data['id']);
        $this->deleteEquipmentRelations($data['id']);
        return $data;
    }

    protected function deleteFileRelations(string|array $id): bool
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

    protected function deleteEquipmentRelations(string|array $id): bool
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
     * Adds parent relation between child and parent.
     * Assumes that conditions were validated before
     *
     * @param string $childId Child equipment id
     * @param string $parentId Parent equipment id
     * @param string $slotId Slot equipment id
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
     * Updates childId parent by removing the old one and adding the new one
     *
     * @param string $childId Child equipment id
     * @param string $parentId Parent equipment id
     * @param string $slotId Slot equipment id
     * 
     * @return bool Always true
     */ 
    public function updateParent(string $childId, string $parentId, int $slotId): bool
    {
        $this->db->transException(true)->transBegin();
        $this->deleteParent($childId);
        $this->addParent($childId, $parentId, $slotId);
        $parent = $this->select('location_id')->find($parentId);
        $child = new Equipment([
            'id' => $childId,
            'location_id' => $parent->location_id
        ]);
        $this->update($childId, $child);
        $this->db->transCommit();
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
        $this->db->transException(true)->transBegin();
        $parentChildModel = model('ParentChildModel');
        $relations = $parentChildModel->where('child_equip_id', $childId)->findAll();
        foreach($relations as $relation){
            $parentChildModel->delete($relation->id, purge: true);
        }
        $this->db->transCommit();
        return true;
    }

    /**
     * Removes parent relation between child and parent.
     * Assumes that conditions were validated before
     *
     * @param string $parentId Parent equipment id
     * 
     * @return bool Always true
     */ 
    public function deleteChildren(string $parentId): bool
    {
        $this->db->transException(true)->transBegin();
        $parentChildModel = model('ParentChildModel');
        $relations = $parentChildModel->where('parent_equip_id', $parentId)->findAll();
        foreach($relations as $relation){
            $parentChildModel->delete($relation->id, purge: true);
        }
        $this->db->transCommit();
        return true;
    }

    /**
     * Disassembles a pair of child and parent
     *
     * @param string $parentId Parent equipment id
     * @param string $childId Child equipment id
     * 
     * @return bool Always true
     */ 
    public function disassemble(string $parentId, string $childId): bool
    {
        $this->db->transException(true)->transBegin();
        $parentChildModel = model('ParentChildModel');
        $relations = $parentChildModel
            ->where('parent_equip_id', $parentId)
            ->where('child_equip_id', $childId)
            ->findAll();
        foreach($relations as $relation){
            $parentChildModel->delete($relation->id, purge: true);
        }
        $this->db->transCommit();
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

    /**
     * Returns true if an equipment has slots
     * if $slotId is given, evaluates for a specific slotId
     *
     * @param string $id  Id of the equipment to check for slot
     * @param ?string $slotId Id of the slot to check
     * 
     * @return bool true if equipment has the specified slot
     */
    public function equipHasSlot(string $id, ?string $slotId=null): bool
    {
        $query = $this->select('1')
            ->join('type_slots ts', 'equipments.type_id=ts.parent_type_id', 'right')
            ->where('equipments.id', $id);
        if (!is_null($slotId)){
            $query = $query->where('ts.id', $slotId);
        }
        $equipHasSlot = $query->first();
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
    public function equipSlotIsEmpty(string $id, string $slotId, ?string $childId=null): bool
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

    public function getChildrenIDs(string $id): array
    {
        $db = db_connect();
        $children = $db->table('parent_child_equips pce')
            ->select('child_equip_id as id')
            ->where('parent_equip_id', $id)
            ->get()->getResult('array');
        $res = [];
        // If there are no children, the method returns an empty array
        //If there are children, checks if the children have children
        foreach ($children as $child){
            $res[] = $child['id'];
            $res = array_merge($res, $this->getChildrenIds($child['id']));
        }
        return $res;
    }
}
