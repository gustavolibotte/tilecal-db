<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class TileCal3DController extends APIController
{
    public function getStatus(): ResponseInterface
    {
        $db = \Config\Database::connect();
        $sql = "
            SELECT dl.name as location_name, e.id as equipment_id, s.name as status_name
            FROM detector_locations dl
            LEFT JOIN equipments e ON e.detector_location_id = dl.id AND e.deleted_at IS NULL
            LEFT JOIN statuses s ON e.status_id = s.id
            WHERE dl.name REGEXP '^(EBA|EBC|LBA|LBC)[0-6][0-9]$' AND dl.name NOT LIKE '%00'
        ";
        
        try {
            $results = $db->query($sql)->getResultArray();
            $data = [
                'EBA' => array_fill(0, 64, null),
                'LBA' => array_fill(0, 64, null),
                'LBC' => array_fill(0, 64, null),
                'EBC' => array_fill(0, 64, null),
            ];
            
            foreach ($results as $row) {
                $barrel = substr($row['location_name'], 0, 3);
                $moduleIndex = (int)substr($row['location_name'], 3, 2) - 1; 
                
                if ($data[$barrel][$moduleIndex] === null) {
                    $data[$barrel][$moduleIndex] = [
                        'status' => 'NOMINAL',
                        'temperature' => 24.5,
                        'powerDraw' => 4.2,
                        'serial' => $row['equipment_id'] ?? 'N/A'
                    ];
                }

                $dbStatus = strtoupper($row['status_name'] ?? 'NOMINAL');
                if (!in_array($dbStatus, ['NOMINAL', 'GOOD', 'OK', 'WORKING', 'INSTALLED', 'TESTED', 'DELIVERED'])) {
                    $data[$barrel][$moduleIndex]['status'] = 'ALERT';
                }
            }
            
            foreach ($data as $barrel => &$modules) {
                foreach ($modules as $index => &$mod) {
                    if ($mod === null) {
                        $mod = ['status' => 'UNKNOWN', 'temperature' => 0, 'powerDraw' => 0, 'serial' => 'EMPTY'];
                    }
                }
            }
            return $this->respondQueryResults($data);
        } catch (\Throwable $e) {
            return $this->failInternalError($e);
        }
    }

    public function getModule(string $locationName): ResponseInterface
    {
        $db = \Config\Database::connect();
        $sql = "
            SELECT e.id as equipment_id, e.type_params, s.name as status_name
            FROM equipments e
            JOIN detector_locations dl ON e.detector_location_id = dl.id
            JOIN equipment_types et ON e.type_id = et.id
            JOIN statuses s ON e.status_id = s.id
            WHERE dl.name = ? AND e.deleted_at IS NULL AND et.name LIKE '%PMT%'
        ";
        try {
            $pmts = $db->query($sql, [$locationName])->getResultArray();
            $channels = [];
            foreach ($pmts as $pmt) {
                $params = json_decode($pmt['type_params'], true) ?? [];
                $channel = $params['phase1_position'] ?? null;
                if ($channel !== null) {
                    $channels[$channel] = [
                        'serial' => $pmt['equipment_id'], 
                        'status' => $pmt['status_name'],
                        'qe' => $params['qe'] ?? 'N/A', 
                        'hv' => $params['nominal_hv'] ?? 'N/A', 
                        'model' => $params['model'] ?? 'N/A'
                    ];
                }
            }
            return $this->respondQueryResults($channels);
        } catch (\Throwable $e) {
            return $this->failInternalError($e);
        }
    }
}