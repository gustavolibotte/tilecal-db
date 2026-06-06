<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class TileCal3DController extends APIController
{
    /**
     * Retrieves the global status and component statistics of all 256 calorimeter modules.
     */
    public function getStatus(): ResponseInterface
    {
        $db = \Config\Database::connect();
        
        $sql = "
            SELECT 
                dl.name as location_name, et.name as type_name, s.name as status_name, COUNT(DISTINCT e.id) as qty
            FROM detector_locations dl
            JOIN equipments e ON e.detector_location_id = dl.id AND e.deleted_at IS NULL
            JOIN equipment_types et ON e.type_id = et.id
            JOIN statuses s ON e.status_id = s.id
            WHERE dl.name REGEXP '^(EBA|EBC|LBA|LBC)[0-6][0-9]$' AND dl.name NOT LIKE '%00'
            GROUP BY dl.name, et.name, s.name
            
            UNION ALL
            
            SELECT 
                dl.name as location_name, parent_et.name as type_name, parent_s.name as status_name, COUNT(DISTINCT parent.id) as qty
            FROM detector_locations dl
            JOIN equipments child ON child.detector_location_id = dl.id AND child.deleted_at IS NULL
            JOIN parent_child_equips pce ON child.id = pce.child_equip_id
            JOIN equipments parent ON pce.parent_equip_id = parent.id AND parent.deleted_at IS NULL
            JOIN equipment_types parent_et ON parent.type_id = parent_et.id
            JOIN statuses parent_s ON parent.status_id = parent_s.id
            WHERE dl.name REGEXP '^(EBA|EBC|LBA|LBC)[0-6][0-9]$' AND dl.name NOT LIKE '%00'
            GROUP BY dl.name, parent_et.name, parent_s.name
        ";
        
        try {
            $results = $db->query($sql)->getResultArray();
            
            $data = [
                'EBA' => array_fill(0, 64, null), 'LBA' => array_fill(0, 64, null),
                'LBC' => array_fill(0, 64, null), 'EBC' => array_fill(0, 64, null),
            ];
            
            foreach (['EBA', 'LBA', 'LBC', 'EBC'] as $b) {
                for ($i = 0; $i < 64; $i++) {
                    $data[$b][$i] = [
                        'status' => 'EMPTY',
                        'stats'  => [],
                        'total'  => 0,
                        'errors' => 0,
                        'warnings' => 0,
                        'nominals' => 0
                    ];
                }
            }

            foreach ($results as $row) {
                $barrel = substr($row['location_name'], 0, 3);
                $moduleIndex = (int)substr($row['location_name'], 3, 2) - 1; 
                
                $type = $row['type_name'];
                $status = strtoupper($row['status_name']);
                $qty = (int)$row['qty'];

                if (!isset($data[$barrel][$moduleIndex]['stats'][$type])) {
                    $data[$barrel][$moduleIndex]['stats'][$type] = [];
                }
                if (!isset($data[$barrel][$moduleIndex]['stats'][$type][$status])) {
                    $data[$barrel][$moduleIndex]['stats'][$type][$status] = 0;
                }
                
                $data[$barrel][$moduleIndex]['stats'][$type][$status] += $qty;
                $data[$barrel][$moduleIndex]['total'] += $qty;

                if (in_array($status, ['FAILURE', 'BROKEN', 'LOST', 'ALERT'])) {
                    $data[$barrel][$moduleIndex]['errors'] += $qty;
                } elseif (in_array($status, ['INSTALLED', 'TESTED', 'DELIVERED', 'NOMINAL', 'GOOD', 'OK', 'WORKING'])) {
                    $data[$barrel][$moduleIndex]['nominals'] += $qty;
                } else {
                    $data[$barrel][$moduleIndex]['warnings'] += $qty; // UNKNOWN, TESTING, DEVELOPMENT etc
                }
            }
            
            foreach (['EBA', 'LBA', 'LBC', 'EBC'] as $b) {
                for ($i = 0; $i < 64; $i++) {
                    $mod = &$data[$b][$i];
                    if ($mod['total'] > 0) {
                        if ($mod['errors'] > 0) {
                            $mod['status'] = 'ALERT';
                        } elseif ($mod['warnings'] > 0) {
                            $mod['status'] = 'WARNING';
                        } else {
                            $mod['status'] = 'NOMINAL';
                        }
                    } else {
                        $mod['status'] = 'EMPTY';
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