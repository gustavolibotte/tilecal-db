<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Statistics extends ResourceController
{
    /**
     * Get the total count of equipments grouped by their equipment type.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getInventoryByType()
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('equipments e');
        $builder->select('et.name as type_name, COUNT(e.id) as total_count');
        $builder->join('equipment_types et', 'e.type_id = et.id', 'left');
        $builder->where('e.deleted_at', null); 
        $builder->groupBy('et.id, et.name');
        $builder->orderBy('total_count', 'DESC');
        
        return $this->respond(['status' => 200, 'data' => $builder->get()->getResultArray()]);
    }

    /**
     * Get the distribution of equipments based on their current status.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getGlobalStatusDistribution()
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('equipments e');
        $builder->select('s.name as status_name, COUNT(e.id) as total_count');
        $builder->join('statuses s', 'e.status_id = s.id', 'left');
        $builder->where('e.deleted_at', null);
        $builder->groupBy('s.id, s.name');
        $builder->orderBy('total_count', 'DESC');
        
        return $this->respond(['status' => 200, 'data' => $builder->get()->getResultArray()]);
    }

    /**
     * Get the distribution of equipments by specific locations.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getLocationDistribution()
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('equipments e');
        $builder->select('l.name as location_name, COUNT(e.id) as total_count');
        $builder->join('locations l', 'e.location_id = l.id', 'left');
        $builder->where('e.deleted_at', null);
        $builder->groupBy('l.id, l.name');
        $builder->orderBy('total_count', 'DESC');
        $builder->limit(10); 
        
        return $this->respond(['status' => 200, 'data' => $builder->get()->getResultArray()]);
    }

    /**
     * Get the distribution of equipments by Institutes.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getInstituteDistribution()
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('equipments e');
        $builder->select('i.name as institute_name, COUNT(e.id) as total_count');
        $builder->join('institutes i', 'e.owner_id = i.id', 'left');
        $builder->where('e.deleted_at', null);
        $builder->groupBy('i.id, i.name');
        $builder->orderBy('total_count', 'DESC');
        
        return $this->respond(['status' => 200, 'data' => $builder->get()->getResultArray()]);
    }

    /**
     * Get counts of "Orphaned" components (Status = Good, but not assembled into any parent).
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getOrphanedComponents()
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('equipments e');
        $builder->select('et.name as type_name, COUNT(e.id) as total_count');
        $builder->join('equipment_types et', 'e.type_id = et.id', 'left');
        $builder->join('parent_child_equips pce', 'e.id = pce.child_equip_id', 'left');
        $builder->join('statuses s', 'e.status_id = s.id', 'left');
        
        $builder->where('e.deleted_at', null);
        $builder->where('pce.id', null); 
        $builder->where('s.name', 'Good'); 
        
        $builder->groupBy('et.id, et.name');
        $builder->orderBy('total_count', 'DESC');
        $builder->limit(10);
        
        return $this->respond(['status' => 200, 'data' => $builder->get()->getResultArray()]);
    }

    /**
     * Get the list of equipments for a specific location.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getEquipmentsByLocation()
    {
        $locationName = $this->request->getGet('location');
        
        if (!$locationName) {
            return $this->fail('Location name is required', 400);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('equipments e');
        
        $builder->select('e.id as equipment_id, et.name as type_name, s.name as status_name, i.name as owner_name');
        
        $builder->join('equipment_types et', 'e.type_id = et.id', 'left');
        $builder->join('statuses s', 'e.status_id = s.id', 'left');
        $builder->join('institutes i', 'e.owner_id = i.id', 'left');
        $builder->join('locations l', 'e.location_id = l.id', 'left');
        
        $builder->where('e.deleted_at', null);
        $builder->where('l.name', $locationName);
        $builder->orderBy('e.id', 'ASC');
        
        $query = $builder->get();
        return $this->respond(['status' => 200, 'data' => $query->getResultArray()]);
    }
}