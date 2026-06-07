<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class TileCal3DController extends APIController
{
    /**
     * Retrieves the global status and component statistics of all 256 calorimeter modules.
     * Maps direct equipment and full hierarchical sub-components.
     */
    public function getStatus(): ResponseInterface
    {
        $db = \Config\Database::connect();
        
        // Comprehensive query using UNION ALL to grab:
        // 1. Direct components (PMTs bound to detector locations)
        // 2. Parents of those components (PMT Blocks)
        // 3. Siblings under the same parents (FENICS and HV Dividers)
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
            JOIN parent_child_equips pce ON child.id = pce.child_equip_id AND pce.deleted_at IS NULL
            JOIN equipments parent ON pce.parent_equip_id = parent.id AND parent.deleted_at IS NULL
            JOIN equipment_types parent_et ON parent.type_id = parent_et.id
            JOIN statuses parent_s ON parent.status_id = parent_s.id
            WHERE dl.name REGEXP '^(EBA|EBC|LBA|LBC)[0-6][0-9]$' AND dl.name NOT LIKE '%00'
            GROUP BY dl.name, parent_et.name, parent_s.name

            UNION ALL

            SELECT 
                dl.name as location_name, sib_et.name as type_name, sib_s.name as status_name, COUNT(DISTINCT sib.id) as qty
            FROM detector_locations dl
            JOIN equipments child ON child.detector_location_id = dl.id AND child.deleted_at IS NULL
            JOIN parent_child_equips pce1 ON child.id = pce1.child_equip_id AND pce1.deleted_at IS NULL
            JOIN parent_child_equips pce2 ON pce1.parent_equip_id = pce2.parent_equip_id AND pce2.deleted_at IS NULL
            JOIN equipments sib ON pce2.child_equip_id = sib.id AND sib.id <> child.id AND sib.deleted_at IS NULL
            JOIN equipment_types sib_et ON sib.type_id = sib_et.id
            JOIN statuses sib_s ON sib.status_id = sib_s.id
            WHERE dl.name REGEXP '^(EBA|EBC|LBA|LBC)[0-6][0-9]$' AND dl.name NOT LIKE '%00'
            GROUP BY dl.name, sib_et.name, sib_s.name
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
                        'status'   => 'EMPTY',
                        'stats'    => [],
                        'total'    => 0,
                        'errors'   => 0,
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

                // Priority Color Mapping Logic
                if (in_array($status, ['FAILURE', 'BROKEN', 'LOST', 'ALERT'])) {
                    $data[$barrel][$moduleIndex]['errors'] += $qty;
                } elseif (in_array($status, ['INSTALLED', 'TESTED', 'DELIVERED', 'NOMINAL', 'GOOD', 'OK', 'WORKING'])) {
                    $data[$barrel][$moduleIndex]['nominals'] += $qty;
                } else {
                    // Triggers warning for UNKNOWN, TESTING, DEVELOPMENT, etc.
                    $data[$barrel][$moduleIndex]['warnings'] += $qty;
                }
            }
            
            // Macro status calculation based on standard hierarchy
            foreach (['EBA', 'LBA', 'LBC', 'EBC'] as $b) {
                for ($i = 0; $i < 64; $i++) {
                    $mod = &$data[$b][$i];
                    if ($mod['total'] > 0) {
                        if ($mod['errors'] > 0) {
                            $mod['status'] = 'ALERT'; // Red
                        } elseif ($mod['warnings'] > 0) {
                            $mod['status'] = 'WARNING'; // Yellow (Resolves the pure Unknown issue)
                        } else {
                            $mod['status'] = 'NOMINAL'; // Green
                        }
                    }
                }
            }
            return $this->respondQueryResults($data);
        } catch (\Throwable $e) {
            return $this->failInternalError($e);
        }
    }

    /**
     * Retrieves the complete electronics readout chain grouped by physical channel.
     */
    public function getModule(string $locationName): ResponseInterface
    {
        $db = \Config\Database::connect();
        
        // Tree traversal matching PMTs with their parent PMT Blocks and sister boards
        $sql = "
            SELECT 
                e.id as pmt_id, s_pmt.name as pmt_status, e.type_params as pmt_params,
                p_block.id as block_id, s_block.name as block_status,
                e_fenics.id as fenics_id, s_fenics.name as fenics_status,
                e_divider.id as divider_id, s_divider.name as divider_status
            FROM equipments e
            JOIN detector_locations dl ON e.detector_location_id = dl.id
            JOIN equipment_types et ON e.type_id = et.id AND et.name = 'PMT'
            JOIN statuses s_pmt ON e.status_id = s_pmt.id
            LEFT JOIN parent_child_equips pce_block ON e.id = pce_block.child_equip_id AND pce_block.deleted_at IS NULL
            LEFT JOIN equipments p_block ON pce_block.parent_equip_id = p_block.id AND p_block.deleted_at IS NULL
            LEFT JOIN statuses s_block ON p_block.status_id = s_block.id
            LEFT JOIN parent_child_equips pce_fenics ON p_block.id = pce_fenics.parent_equip_id AND pce_fenics.deleted_at IS NULL
            LEFT JOIN equipments e_fenics ON pce_fenics.child_equip_id = e_fenics.id AND e_fenics.deleted_at IS NULL
            LEFT JOIN equipment_types et_fenics ON e_fenics.type_id = et_fenics.id AND et_fenics.name LIKE 'FENICS%'
            LEFT JOIN statuses s_fenics ON e_fenics.status_id = s_fenics.id
            LEFT JOIN parent_child_equips pce_divider ON p_block.id = pce_divider.parent_equip_id AND pce_divider.deleted_at IS NULL
            LEFT JOIN equipments e_divider ON pce_divider.child_equip_id = e_divider.id AND e_divider.deleted_at IS NULL
            LEFT JOIN equipment_types et_divider ON e_divider.type_id = et_divider.id AND et_divider.name LIKE 'Active HV Divider%'
            LEFT JOIN statuses s_divider ON e_divider.status_id = s_divider.id
            WHERE dl.name = ? AND e.deleted_at IS NULL
        ";
        
        try {
            $pmts = $db->query($sql, [$locationName])->getResultArray();
            $channels = [];
            
            foreach ($pmts as $pmt) {
                $params = json_decode($pmt['pmt_params'], true) ?? [];
                $channel = $params['phase1_position'] ?? null;
                
                if ($channel !== null) {
                    $channels[$channel] = [
                        'pmt' => [
                            'serial' => $pmt['pmt_id'], 'status' => $pmt['pmt_status'],
                            'model'  => $params['model'] ?? 'N/A', 'qe' => $params['qe'] ?? 'N/A', 'hv' => $params['nominal_hv'] ?? 'N/A'
                        ],
                        'block'   => $pmt['block_id'] ? ['serial' => $pmt['block_id'], 'status' => $pmt['block_status']] : null,
                        'fenics'  => $pmt['fenics_id'] ? ['serial' => $pmt['fenics_id'], 'status' => $pmt['fenics_status']] : null,
                        'divider' => $pmt['divider_id'] ? ['serial' => $pmt['divider_id'], 'status' => $pmt['divider_status']] : null
                    ];
                }
            }
            return $this->respondQueryResults($channels);
        } catch (\Throwable $e) {
            return $this->failInternalError($e);
        }
    }
}