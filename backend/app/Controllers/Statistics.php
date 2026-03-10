<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Statistics extends ResourceController
{
    /**
     * Get the total count of equipments grouped by their equipment type.
     * * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getInventoryByType()
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('equipments e');
        $builder->select('et.name as type_name, COUNT(e.id) as total_count');
        $builder->join('equipment_types et', 'e.equipment_type_id = et.id', 'left');
        $builder->where('e.deleted_at', null); // Exclude soft-deleted items
        $builder->groupBy('et.id, et.name');
        $builder->orderBy('total_count', 'DESC');
        
        $query = $builder->get();
        $results = $query->getResultArray();

        return $this->respond([
            'status' => 200,
            'data'   => $results
        ]);
    }

    /**
     * Get the distribution of equipments based on their current status.
     * * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getGlobalStatusDistribution()
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('equipments e');
        $builder->select('s.name as status_name, COUNT(e.id) as total_count');
        $builder->join('status s', 'e.status_id = s.id', 'left');
        $builder->where('e.deleted_at', null);
        $builder->groupBy('s.id, s.name');
        $builder->orderBy('total_count', 'DESC');
        
        $query = $builder->get();
        $results = $query->getResultArray();

        return $this->respond([
            'status' => 200,
            'data'   => $results
        ]);
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
        // Limitamos a 10 para o gráfico não ficar gigante e ilegível
        $builder->limit(10); 
        
        $query = $builder->get();
        return $this->respond(['status' => 200, 'data' => $query->getResultArray()]);
    }

    /**
     * Get the distribution of equipments by Institutes.
     * Requires joining equipments -> locations -> institutes.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getInstituteDistribution()
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('equipments e');
        $builder->select('i.name as institute_name, COUNT(e.id) as total_count');
        $builder->join('locations l', 'e.location_id = l.id', 'left');
        $builder->join('institutes i', 'l.institute_id = i.id', 'left');
        $builder->where('e.deleted_at', null);
        $builder->groupBy('i.id, i.name');
        $builder->orderBy('total_count', 'DESC');
        
        $query = $builder->get();
        return $this->respond(['status' => 200, 'data' => $query->getResultArray()]);
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
        $builder->join('equipment_types et', 'e.equipment_type_id = et.id', 'left');
        // Join with parent_child_relation to find active assembly links
        $builder->join('parent_child_relation pcr', 'e.id = pcr.child_id AND pcr.unrelated_at IS NULL', 'left');
        $builder->join('status s', 'e.status_id = s.id', 'left');
        
        $builder->where('e.deleted_at', null);
        $builder->where('pcr.id', null); // The component is NOT a child in any active relation
        $builder->where('s.name', 'Good'); // Only count healthy components waiting for assembly
        
        $builder->groupBy('et.id, et.name');
        $builder->orderBy('total_count', 'DESC');
        $builder->limit(10);
        
        $query = $builder->get();
        return $this->respond(['status' => 200, 'data' => $query->getResultArray()]);
    }
}