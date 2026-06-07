<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class TileCal3DController extends APIController
{
    public function getStatus(): ResponseInterface
    {
        $db = \Config\Database::connect();
        
        try {
            $locations = $db->query("
                SELECT id, name FROM detector_locations 
                WHERE name REGEXP '^(EBA|EBC|LBA|LBC)[0-6][0-9]$' AND name NOT LIKE '%00'
            ")->getResultArray();
            
            $locMap = [];
            foreach ($locations as $loc) {
                $locMap[$loc['id']] = $loc['name'];
            }

            $equipments = $db->query("
                SELECT e.id, e.detector_location_id, et.name as type, s.name as status
                FROM equipments e
                JOIN equipment_types et ON e.type_id = et.id
                JOIN statuses s ON e.status_id = s.id
                WHERE e.deleted_at IS NULL
            ")->getResultArray();

            $equipMap = [];
            $locToEquips = [];
            foreach ($equipments as $e) {
                $equipMap[$e['id']] = $e;
                if ($e['detector_location_id'] && isset($locMap[$e['detector_location_id']])) {
                    $locToEquips[$e['detector_location_id']][] = $e['id'];
                }
            }

            $relations = $db->query("
                SELECT parent_equip_id as p, child_equip_id as c
                FROM parent_child_equips
                WHERE deleted_at IS NULL
            ")->getResultArray();

            $childrenOf = [];
            $parentsOf = [];
            foreach ($relations as $r) {
                $childrenOf[$r['p']][] = $r['c'];
                $parentsOf[$r['c']][] = $r['p'];
            }

            $data = [
                'EBA' => array_fill(0, 64, null), 'LBA' => array_fill(0, 64, null),
                'LBC' => array_fill(0, 64, null), 'EBC' => array_fill(0, 64, null),
            ];
            foreach (['EBA', 'LBA', 'LBC', 'EBC'] as $b) {
                for ($i = 0; $i < 64; $i++) {
                    $data[$b][$i] = ['status' => 'EMPTY', 'stats' => [], 'total' => 0, 'errors' => 0, 'warnings' => 0, 'nominals' => 0];
                }
            }

            foreach ($locMap as $locId => $locName) {
                $barrel = substr($locName, 0, 3);
                $moduleIndex = (int)substr($locName, 3, 2) - 1;
                
                $visited = [];
                $queue = $locToEquips[$locId] ?? [];
                
                foreach ($queue as $q) { $visited[$q] = true; }
                
                while (!empty($queue)) {
                    $curr = array_shift($queue);
                    
                    if (isset($parentsOf[$curr])) {
                        foreach ($parentsOf[$curr] as $p) {
                            if (!isset($visited[$p])) { $visited[$p] = true; $queue[] = $p; }
                        }
                    }
                    if (isset($childrenOf[$curr])) {
                        foreach ($childrenOf[$curr] as $c) {
                            if (!isset($visited[$c])) { $visited[$c] = true; $queue[] = $c; }
                        }
                    }
                }
                
                foreach ($visited as $eqId => $true) {
                    if (!isset($equipMap[$eqId])) continue;
                    $eq = $equipMap[$eqId];
                    $type = $eq['type'];
                    $status = strtoupper($eq['status']);
                    
                    if (!isset($data[$barrel][$moduleIndex]['stats'][$type])) {
                        $data[$barrel][$moduleIndex]['stats'][$type] = [];
                    }
                    if (!isset($data[$barrel][$moduleIndex]['stats'][$type][$status])) {
                        $data[$barrel][$moduleIndex]['stats'][$type][$status] = 0;
                    }
                    
                    $data[$barrel][$moduleIndex]['stats'][$type][$status]++;
                    $data[$barrel][$moduleIndex]['total']++;
                    
                    if (in_array($status, ['FAILURE', 'BROKEN', 'LOST', 'ALERT'])) {
                        $data[$barrel][$moduleIndex]['errors']++;
                    } elseif (in_array($status, ['INSTALLED', 'TESTED', 'DELIVERED', 'NOMINAL', 'GOOD', 'OK', 'WORKING'])) {
                        $data[$barrel][$moduleIndex]['nominals']++;
                    } else {
                        $data[$barrel][$moduleIndex]['warnings']++;
                    }
                }
                
                $mod = &$data[$barrel][$moduleIndex];
                if ($mod['total'] > 0) {
                    if ($mod['errors'] > 0) $mod['status'] = 'ALERT';
                    elseif ($mod['warnings'] > 0) $mod['status'] = 'WARNING';
                    else $mod['status'] = 'NOMINAL';
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