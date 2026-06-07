<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class TileCal3DController extends APIController
{
    public function getStatus(): ResponseInterface
    {
        $db = \Config\Database::connect();
        
        $sql = "
            SELECT loc as location_name, type as type_name, status as status_name, COUNT(DISTINCT id) as qty
            FROM (
                SELECT dl.name as loc, et.name as type, s.name as status, e.id
                FROM detector_locations dl JOIN equipments e ON e.detector_location_id = dl.id JOIN equipment_types et ON e.type_id = et.id JOIN statuses s ON e.status_id = s.id
                WHERE dl.name REGEXP '^(EBA|EBC|LBA|LBC)[0-6][0-9]$' AND e.deleted_at IS NULL
                UNION
                SELECT dl.name as loc, et.name as type, s.name as status, p.id
                FROM detector_locations dl JOIN equipments e ON e.detector_location_id = dl.id JOIN parent_child_equips pce ON e.id = pce.child_equip_id JOIN equipments p ON pce.parent_equip_id = p.id JOIN equipment_types et ON p.type_id = et.id JOIN statuses s ON p.status_id = s.id
                WHERE dl.name REGEXP '^(EBA|EBC|LBA|LBC)[0-6][0-9]$' AND pce.deleted_at IS NULL AND p.deleted_at IS NULL
                UNION
                SELECT dl.name as loc, et.name as type, s.name as status, gp.id
                FROM detector_locations dl JOIN equipments e ON e.detector_location_id = dl.id JOIN parent_child_equips pce1 ON e.id = pce1.child_equip_id JOIN parent_child_equips pce2 ON pce1.parent_equip_id = pce2.child_equip_id JOIN equipments gp ON pce2.parent_equip_id = gp.id JOIN equipment_types et ON gp.type_id = et.id JOIN statuses s ON gp.status_id = s.id
                WHERE dl.name REGEXP '^(EBA|EBC|LBA|LBC)[0-6][0-9]$' AND pce1.deleted_at IS NULL AND pce2.deleted_at IS NULL AND gp.deleted_at IS NULL
                UNION
                SELECT dl.name as loc, et.name as type, s.name as status, c.id
                FROM detector_locations dl JOIN equipments e ON e.detector_location_id = dl.id JOIN parent_child_equips pce ON e.id = pce.parent_equip_id JOIN equipments c ON pce.child_equip_id = c.id JOIN equipment_types et ON c.type_id = et.id JOIN statuses s ON c.status_id = s.id
                WHERE dl.name REGEXP '^(EBA|EBC|LBA|LBC)[0-6][0-9]$' AND pce.deleted_at IS NULL AND c.deleted_at IS NULL
                UNION
                SELECT dl.name as loc, et.name as type, s.name as status, gc.id
                FROM detector_locations dl JOIN equipments e ON e.detector_location_id = dl.id JOIN parent_child_equips pce1 ON e.id = pce1.parent_equip_id JOIN parent_child_equips pce2 ON pce1.child_equip_id = pce2.parent_equip_id JOIN equipments gc ON pce2.child_equip_id = gc.id JOIN equipment_types et ON gc.type_id = et.id JOIN statuses s ON gc.status_id = s.id
                WHERE dl.name REGEXP '^(EBA|EBC|LBA|LBC)[0-6][0-9]$' AND pce1.deleted_at IS NULL AND pce2.deleted_at IS NULL AND gc.deleted_at IS NULL
            ) as combined
            GROUP BY loc, type, status
        ";
        
        try {
            $results = $db->query($sql)->getResultArray();
            $data = [
                'EBA' => array_fill(0, 64, null), 'LBA' => array_fill(0, 64, null),
                'LBC' => array_fill(0, 64, null), 'EBC' => array_fill(0, 64, null),
            ];
            
            foreach (['EBA', 'LBA', 'LBC', 'EBC'] as $b) {
                for ($i = 0; $i < 64; $i++) {
                    $data[$b][$i] = ['status' => 'EMPTY', 'stats' => [], 'total' => 0, 'errors' => 0, 'warnings' => 0, 'nominals' => 0];
                }
            }

            foreach ($results as $row) {
                $barrel = substr($row['location_name'], 0, 3);
                $moduleIndex = (int)substr($row['location_name'], 3, 2) - 1; 
                $type = $row['type_name'];
                $status = strtoupper($row['status_name']);
                $qty = (int)$row['qty'];

                if (!isset($data[$barrel][$moduleIndex]['stats'][$type])) $data[$barrel][$moduleIndex]['stats'][$type] = [];
                if (!isset($data[$barrel][$moduleIndex]['stats'][$type][$status])) $data[$barrel][$moduleIndex]['stats'][$type][$status] = 0;
                
                $data[$barrel][$moduleIndex]['stats'][$type][$status] += $qty;
                $data[$barrel][$moduleIndex]['total'] += $qty;

                if (in_array($status, ['FAILURE', 'BROKEN', 'LOST', 'ALERT'])) {
                    $data[$barrel][$moduleIndex]['errors'] += $qty;
                } elseif (in_array($status, ['INSTALLED', 'TESTED', 'DELIVERED', 'NOMINAL', 'GOOD', 'OK', 'WORKING'])) {
                    $data[$barrel][$moduleIndex]['nominals'] += $qty;
                } else {
                    $data[$barrel][$moduleIndex]['warnings'] += $qty;
                }
            }
            
            foreach (['EBA', 'LBA', 'LBC', 'EBC'] as $b) {
                for ($i = 0; $i < 64; $i++) {
                    $mod = &$data[$b][$i];
                    if ($mod['total'] > 0) {
                        if ($mod['errors'] > 0) $mod['status'] = 'ALERT';
                        elseif ($mod['warnings'] > 0) $mod['status'] = 'WARNING';
                        else $mod['status'] = 'NOMINAL';
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
        
        try {
            $baseEquips = $db->query("
                SELECT e.id, e.type_params, s.name as status, et.name as type
                FROM equipments e JOIN detector_locations dl ON e.detector_location_id = dl.id
                JOIN equipment_types et ON e.type_id = et.id JOIN statuses s ON e.status_id = s.id
                WHERE dl.name = ? AND e.deleted_at IS NULL
            ", [$locationName])->getResultArray();

            $allEquips = [];
            foreach($baseEquips as $e) $allEquips[$e['id']] = $e;
            
            $knownIds = array_column($baseEquips, 'id');
            $allRelations = [];
            
            for ($i=0; $i<3; $i++) {
                if (empty($knownIds)) break;
                $in = "'".implode("','", $knownIds)."'";
                
                $rels = $db->query("
                    SELECT pce.parent_equip_id, pce.child_equip_id, c.type_params, s.name as status, et.name as type
                    FROM parent_child_equips pce JOIN equipments c ON pce.child_equip_id = c.id AND c.deleted_at IS NULL
                    JOIN equipment_types et ON c.type_id = et.id JOIN statuses s ON c.status_id = s.id
                    WHERE pce.parent_equip_id IN ($in) AND pce.deleted_at IS NULL
                ")->getResultArray();
                
                foreach($rels as $r) {
                    if (!isset($allEquips[$r['child_equip_id']])) {
                        $allEquips[$r['child_equip_id']] = ['id' => $r['child_equip_id'], 'type_params' => $r['type_params'], 'status' => $r['status'], 'type' => $r['type']];
                        $knownIds[] = $r['child_equip_id'];
                    }
                    $allRelations[$r['parent_equip_id']][$r['child_equip_id']] = true;
                }
                
                $rels = $db->query("
                    SELECT pce.parent_equip_id, pce.child_equip_id, p.type_params, s.name as status, et.name as type
                    FROM parent_child_equips pce JOIN equipments p ON pce.parent_equip_id = p.id AND p.deleted_at IS NULL
                    JOIN equipment_types et ON p.type_id = et.id JOIN statuses s ON p.status_id = s.id
                    WHERE pce.child_equip_id IN ($in) AND pce.deleted_at IS NULL
                ")->getResultArray();
                
                foreach($rels as $r) {
                    if (!isset($allEquips[$r['parent_equip_id']])) {
                        $allEquips[$r['parent_equip_id']] = ['id' => $r['parent_equip_id'], 'type_params' => $r['type_params'], 'status' => $r['status'], 'type' => $r['type']];
                        $knownIds[] = $r['parent_equip_id'];
                    }
                    $allRelations[$r['parent_equip_id']][$r['child_equip_id']] = true;
                }
                $knownIds = array_unique($knownIds);
            }

            $drawersMap = [];
            $blocksMap = [];
            $orphans = ['id' => 'N/A', 'type' => 'Unlinked Components / Bad DB Registration', 'status' => 'WARNING', 'boards' => [], 'blocks' => []];

            $findParent = function($childId, $typeKeyword) use (&$allRelations, &$allEquips) {
                foreach ($allRelations as $pId => $children) {
                    if (isset($children[$childId]) && strpos($allEquips[$pId]['type'], $typeKeyword) !== false) return $pId;
                }
                return null;
            };

            foreach ($allEquips as $id => $e) {
                if (strpos($e['type'], 'Drawer') !== false) {
                    $drawersMap[$id] = ['id' => $id, 'type' => $e['type'], 'status' => $e['status'], 'boards' => [], 'blocks' => []];
                }
            }

            foreach ($allEquips as $id => $e) {
                $type = $e['type'];
                if (strpos($type, 'Block') !== false) {
                    $dId = $findParent($id, 'Drawer');
                    $blocksMap[$id] = ['id' => $id, 'type' => $type, 'status' => $e['status'], 'drawer_id' => $dId, 'channel' => null, 'components' => []];
                } elseif (strpos($type, 'Mainboard') !== false || strpos($type, 'Daughterboard') !== false || strpos($type, 'Board') !== false) {
                    $dId = $findParent($id, 'Drawer');
                    $board = ['id' => $id, 'type' => $type, 'status' => $e['status']];
                    if ($dId && isset($drawersMap[$dId])) $drawersMap[$dId]['boards'][] = $board;
                    else $orphans['boards'][] = $board;
                } elseif (in_array($type, ['PMT', 'FENICS (Short Cable)', 'FENICS (Long Cable)', 'Active HV Divider (Short Cable)', 'Active HV Divider (Long Cable)'])) {
                    $params = json_decode($e['type_params'], true) ?? [];
                    $ch = $params['phase1_position'] ?? null;
                    $bId = $findParent($id, 'Block');
                    
                    $comp = ['id' => $id, 'type' => $type, 'status' => $e['status'], 'model' => $params['model'] ?? 'N/A', 'qe' => $params['qe'] ?? 'N/A', 'hv' => $params['nominal_hv'] ?? 'N/A', 'channel' => $ch];
                    
                    if ($bId) {
                        if (!isset($blocksMap[$bId])) $blocksMap[$bId] = ['id' => $bId, 'type' => 'PMT Block', 'status' => 'UNKNOWN', 'drawer_id' => null, 'channel' => $ch, 'components' => []];
                        $blocksMap[$bId]['components'][] = $comp;
                        if ($ch) $blocksMap[$bId]['channel'] = $ch;
                    } else {
                        $vId = 'orphan_block_' . ($ch ?? 'X') . '_' . $id;
                        $blocksMap[$vId] = ['id' => 'N/A', 'type' => 'Orphaned Channel', 'status' => 'WARNING', 'drawer_id' => null, 'channel' => $ch, 'components' => [$comp]];
                    }
                }
            }

            foreach ($blocksMap as $bId => $b) {
                $dId = $b['drawer_id'];
                if ($dId && isset($drawersMap[$dId])) $drawersMap[$dId]['blocks'][] = $b;
                else $orphans['blocks'][] = $b;
            }

            if (!empty($orphans['blocks']) || !empty($orphans['boards'])) {
                $drawersMap['orphans'] = $orphans;
            }

            foreach ($drawersMap as &$d) {
                usort($d['blocks'], function($a, $b) { return ((int)($a['channel'] ?? 999)) <=> ((int)($b['channel'] ?? 999)); });
            }

            return $this->respondQueryResults(array_values($drawersMap));
        } catch (\Throwable $e) {
            return $this->failInternalError($e);
        }
    }
}