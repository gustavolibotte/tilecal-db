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
}