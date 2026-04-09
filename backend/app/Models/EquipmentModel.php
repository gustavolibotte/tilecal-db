<?php

namespace App\Models;

use App\Entities\ParentChildRelation;
use App\Entities\Equipment;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use TypeError;

class EquipmentModel extends LogModel
{
    protected $table = 'equipments';
    protected $returnType = Equipment::class;
    protected $allowedFields = [
        'id',
        'type_id',
        'type_name',
        'owner_id',
        'owner_mne',
        'location_id',
        'location_name',
        'status_id',
        'status_name',
        'serial',
        'comments',
        'type_params',
        'detector_location_id',
        'detector_location_name'
    ];

    public function initialize()
    {
        parent::initialize();
        helper(['app_array']);
        $this->beforeInsert = array_merge(['substituteFKs'], $this->beforeInsert);
        $this->beforeUpdate = array_merge(['substituteFKs'], $this->beforeUpdate);
        $this->afterDelete[] = 'deleteRelations';
    }

    public function fullQuery(BaseBuilder|Model $queryBuilder = null): BaseBuilder|Model
    {
        if (is_null($queryBuilder)) {
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

    public function joinFiles(BaseBuilder|Model $queryBuilder = null): BaseBuilder|Model
    {
        if (is_null($queryBuilder)) {
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
            $this->db->escape('parent')
        );
        $whereClause = is_array($id) ? 'whereIn' : 'where';
        $parentQuery = $this->fullQuery(db_connect()->table('equipments'))
                    ->select($parentExtra)
                    ->join('parent_child_equips pce', 'equipments.id=pce.parent_equip_id', 'left')
                    ->join('type_slots ts', 'pce.slot_id=ts.id', 'left')
            ->$whereClause('pce.child_equip_id', $id);

        $extraQuery = sprintf(
            "%s as relation_type, ts.name as slot_name",
            $this->db->escape('child')
        );
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
        if (is_null($parentRelation)) {
            return null;
        } else {
            return $parentRelation;
        }
    }

    public function insert($data = null, bool $returnID = true)
    {
        $this->db->transException(true)->transBegin();
        $res = parent::insert($data, true);
        $this->db->transCommit();
        return $res;
    }

    public function update($id = null, $data = null): bool
    {
        $this->db->transException(true)->transBegin();
        $res = parent::update($id, $data);
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
        $whereClause = (is_array($id)) ? 'whereIn' : 'where';
        $relations = $equipments2FilesModel
                    ->select('id')
            ->$whereClause('equipment_id', $id)
                ->findAll();
        foreach ($relations as $relation) {
            $equipments2FilesModel->delete($relation->id, true);
        }
        return true;
    }

    protected function deleteEquipmentRelations(string|array $id): bool
    {
        $whereClause = (is_array($id)) ? 'orWhereIn' : 'orWhere';
        $parentChildModel = model('ParentChildModel');
        $relations = $parentChildModel
            ->$whereClause('parent_equip_id', $id)
            ->$whereClause('child_equip_id', $id)
                ->findAll();
        foreach ($relations as $relation) {
            $parentChildModel->update($relation->id, ['user' => \Config\Services::user()->getUsername()]);
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
    public function addParent(string $childId,
                              string $parentId,
                              int $slotId,
                              bool $updateStatus = false): bool
    {
        $parentChildModel = model('ParentChildModel');
        $parentChildModel->insert([
            'parent_equip_id' => $parentId,
            'child_equip_id' => $childId,
            'slot_id' => $slotId
        ]);
        if ($updateStatus){
            $statusModel = model('StatusModel');
            $status = $statusModel->where('name', 'Assembled')->first();
            $child = new Equipment([
                'id' => $childId,
                'status_id' => $status->id
            ]);
            $this->update($childId, $child);
        }
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
        $statusModel = model('StatusModel');
        $status = $statusModel->where('name', 'Installed')->first();
        $child = new Equipment([
            'id' => $childId,
            'location_id' => $parent->location_id,
            'status_id' => $status->id
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
    public function deleteParent(string $childId,
                                 bool $updateStatus = false): bool
    {
        $this->db->transException(true)->transBegin();
        $parentChildModel = model('ParentChildModel');
        $relations = $parentChildModel->where('child_equip_id', $childId)->findAll();
        foreach ($relations as $relation) {
            $parentChildModel->delete($relation->id, purge: true);
        }
        if ($updateStatus){
            $statusModel = model('StatusModel');
            $status = $statusModel->where('name', 'Stored')->first();
            $child = new Equipment([
                'id' => $childId,
                'status_id' => $status->id
            ]);
            $this->update($childId, $child);
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
        foreach ($relations as $relation) {
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
        foreach ($relations as $relation) {
            $parentChildModel->delete($relation->id, purge: true);
        }
        $statusModel = model('StatusModel');
        $status = $statusModel->where('name', 'Stored')->first();
        $child = new Equipment([
            'id' => $childId,
            'status_id' => $status->id
        ]);
        $this->update($childId, $child);
        $this->db->transCommit();
        return true;
    }

    protected function mergeTypeParams(array $data): array
    {
        if (!array_key_exists('type_params', $data)) {
            return $data;
        }
        $typeParams = json_decode(remove_value('type_params', $data), true);
        foreach ($typeParams as $key => $value) {
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
    public function withDeletedJoin(array $joinedTables): self
    {
        if ($this->tempUseSoftDeletes) {
            foreach ($joinedTables as $tableName) {
                $this->where(
                    sprintf(
                        '`%s`.`%s` IS NULL',
                        $tableName,
                        $this->deletedField
                    )
                );
            }
        }
        return $this;
    }

    /**
     * Returns true if the equipment has a given slot.
     *
     * @param string $id  Id of the equipment to check for slot
     * @param string $slotName Name of the slot to check
     * 
     * @return bool true if equipment has the specified slot
     */
    public function equipHasSlot(string $id, string $slotName): bool
    {
        $res = $this->builder('equipments e')
            ->select('1')
            ->join('type_slots ts', 'e.type_id=ts.parent_type_id', 'right')
            ->where('e.id', $id)
            ->where('ts.name', $slotName)
            ->get()->getResult('array');
        return !empty($res);
    }

    /**
     * Returns true if the equipment slot is empty.
     * Doesn't check if the equipment can actually have the slot.
     *
     * @param string $id  Id of the equipment to check for slot
     * @param string $slotName Id of the slot to check
     * 
     * @return bool true if equipment has the specified slot empty
     */
    public function equipSlotIsEmpty(string $id, string $slotName): bool
    {
        $res = $this->builder('parent_child_equips pce')
            ->select('1')
            ->join('type_slots ts', 'pce.slot_id=ts.id', 'left')
            ->where('pce.parent_equip_id', $id)
            ->where('ts.name', $slotName)
            ->get()->getResult('array');
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
        foreach ($children as $child) {
            $res[] = $child['id'];
            $res = array_merge($res, $this->getChildrenIds($child['id']));
        }
        return $res;
    }

    public function equipIsAssembledInto(string $id): bool
    {
        $db = db_connect();
        $res = $this->builder('parent_child_equips pce')
            ->select('1')
            ->where('child_equip_id', $id)
            ->get()->getResult('array');
        return !empty($res);
    }

    public function substituteFKs(array $data): array
    {
        $data['data'] = $this->substituteFK(
            'type_id',
            ['type_name'],
            ['name'],
            $data['data'],
            model('EquipmentTypeModel')
        );
        $data['data'] = $this->substituteFK(
            'owner_id',
            ['owner_mne'],
            ['mne'],
            $data['data'],
            model('InstituteModel')
        );
        $data['data'] = $this->substituteFK(
            'location_id',
            ['location_name'],
            ['name'],
            $data['data'],
            model('LocationModel')
        );
        $data['data'] = $this->substituteFK(
            'status_id',
            ['status_name'],
            ['name'],
            $data['data'],
            model('StatusModel')
        );
        $data['data'] = $this->substituteFK(
            'detector_location_id',
            ['detector_location_name'],
            ['name'],
            $data['data'],
            model('DetectorLocationModel')
        );
        return $data;
    }

    /**
     * Cascades a location update to all child equipments recursively.
     * Ensures that if a parent (e.g. PMT Block) is moved, all assembled components 
     * inherit the new location automatically.
     *
     * @param string $parentId The parent equipment ID
     * @param string $locationName The new location name
     * @return bool
     */
    public function updateLocationCascade(string $parentId, string $locationName): bool
    {
        $childrenIds = $this->getChildrenIDs($parentId);
        if (empty($childrenIds)) {
            return true;
        }

        $location = model('LocationModel')->where('name', $locationName)->first();
        if (!$location) {
            return false;
        }

        $this->db->transException(true)->transBegin();
        try {
            foreach ($childrenIds as $childId) {
                // Check current location to avoid unnecessary updates and history logs
                $childInfo = $this->select('location_id')->find($childId);
                if ($childInfo && $childInfo->location_id !== $location->id) {
                    $child = new Equipment([
                        'id' => $childId,
                        'location_name' => $locationName
                    ]);
                    // Update triggers beforeUpdate/afterUpdate events and History logging
                    $this->update($childId, $child);
                }
            }
            $this->db->transCommit();
            return true;
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Identifies assembled groups (e.g., PMT Blocks) where the parent and 
     * its children do not share the exact same location.
     *
     * @param string $parentTypeName Standard is 'PMT Block'
     * @return array
     */
    public function getInconsistentAssemblies(string $parentTypeName = 'PMT Block'): array
    {
        $db = db_connect();
        $parents = $this->select('equipments.id, equipments.type_name, locations.name as location_name')
                        ->join('locations', 'equipments.location_id = locations.id', 'left')
                        ->join('equipment_types', 'equipments.type_id = equipment_types.id')
                        ->where('equipment_types.name', $parentTypeName)
                        ->where('equipments.deleted_at IS NULL')
                        ->findAll();

        $inconsistentAssemblies = [];

        foreach ($parents as $parent) {
            $children = $db->table('parent_child_equips pce')
                           ->select('e.id, et.name as type_name, l.name as location_name, ts.name as slot_name')
                           ->join('equipments e', 'pce.child_equip_id = e.id')
                           ->join('equipment_types et', 'e.type_id = et.id')
                           ->join('locations l', 'e.location_id = l.id', 'left')
                           ->join('type_slots ts', 'pce.slot_id = ts.id', 'left')
                           ->where('pce.parent_equip_id', $parent->id)
                           ->where('e.deleted_at IS NULL')
                           ->get()->getResultArray();

            if (empty($children)) continue;

            $isInconsistent = false;
            $parentLoc = $parent->location_name;

            foreach ($children as $child) {
                if ($child['location_name'] !== $parentLoc) {
                    $isInconsistent = true;
                    break;
                }
            }

            if ($isInconsistent) {
                $inconsistentAssemblies[] = [
                    'parent' => $parent->toArray(),
                    'children' => $children
                ];
            }
        }
        return $inconsistentAssemblies;
    }
}