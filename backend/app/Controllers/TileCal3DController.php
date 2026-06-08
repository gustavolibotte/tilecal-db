<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class TileCal3DController extends APIController
{
    public function getStatus(): ResponseInterface
    {
        $db = \Config\Database::connect();
        
        $sql = "
            SELECT dl.name as location_name, et.name as type_name, s.name as status_name, COUNT(DISTINCT e.id) as qty
            FROM detector_locations dl
            JOIN equipments e ON e.detector_location_id = dl.id AND e.deleted_at IS NULL
            JOIN equipment_types et ON e.type_id = et.id
            JOIN statuses s ON e.status_id = s.id
            WHERE dl.name REGEXP '^(EBA|EBC|LBA|LBC)[0-6][0-9]$' AND dl.name NOT LIKE '%00'
            GROUP BY dl.name, et.name, s.name
            
            UNION ALL
            
            SELECT dl.name as location_name, parent_et.name as type_name, parent_s.name as status_name, COUNT(DISTINCT parent.id) as qty
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
            $loc = $db->query("SELECT id FROM detector_locations WHERE name = ?", [$locationName])->getRowArray();
            if (!$loc) return $this->respondQueryResults([]);
            $locId = $loc['id'];

            $directs = $db->query("SELECT id FROM equipments WHERE detector_location_id = ? AND deleted_at IS NULL", [$locId])->getResultArray();
            $knownIds = array_column($directs, 'id');
            
            if (empty($knownIds)) return $this->respondQueryResults([]);

            $relations = [];
            for ($i=0; $i<6; $i++) {
                if (empty($knownIds)) break;
                $in = "'".implode("','", $knownIds)."'";
                $rels = $db->query("
                    SELECT parent_equip_id as p, child_equip_id as c 
                    FROM parent_child_equips 
                    WHERE (parent_equip_id IN ($in) OR child_equip_id IN ($in)) AND deleted_at IS NULL
                ")->getResultArray();
                
                $newIds = [];
                foreach($rels as $r) {
                    $relations[$r['p']][$r['c']] = true;
                    $relations[$r['c']][$r['p']] = true; 
                    $newIds[] = $r['p']; $newIds[] = $r['c'];
                }
                $knownIds = array_unique(array_merge($knownIds, $newIds));
            }

            $in = "'".implode("','", $knownIds)."'";
            $equips = $db->query("
                SELECT e.id, e.type_params, et.name as type, s.name as status
                FROM equipments e JOIN equipment_types et ON e.type_id = et.id JOIN statuses s ON e.status_id = s.id
                WHERE e.id IN ($in) AND e.deleted_at IS NULL
            ")->getResultArray();

            $equipMap = [];
            foreach ($equips as $e) $equipMap[$e['id']] = $e;

            $drawersMap = [];
            $blocksMap = [];
            $orphans = ['id' => 'N/A', 'type' => 'Unlinked Components / Bad DB Registration', 'status' => 'WARNING', 'boards' => [], 'blocks' => []];

            $getParent = function($childId, $typeKeyword) use (&$relations, &$equipMap) {
                foreach ($relations as $pId => $children) {
                    if (isset($children[$childId]) && isset($equipMap[$pId]) && strpos($equipMap[$pId]['type'], $typeKeyword) !== false) return $pId;
                }
                return null;
            };

            foreach ($equipMap as $id => $e) {
                if (strpos($e['type'], 'Drawer') !== false) {
                    $drawersMap[$id] = ['id' => $id, 'type' => $e['type'], 'status' => $e['status'], 'boards' => [], 'blocks' => []];
                }
            }

            foreach ($equipMap as $id => $e) {
                $type = $e['type'];
                if (strpos($type, 'Block') !== false) {
                    $dId = $getParent($id, 'Drawer');
                    $blocksMap[$id] = ['id' => $id, 'type' => $type, 'status' => $e['status'], 'drawer_id' => $dId, 'channel' => null, 'components' => []];
                } elseif (strpos($type, 'Mainboard') !== false || strpos($type, 'Daughterboard') !== false || strpos($type, 'Board') !== false) {
                    $dId = $getParent($id, 'Drawer');
                    $board = ['id' => $id, 'type' => $type, 'status' => $e['status']];
                    if ($dId && isset($drawersMap[$dId])) $drawersMap[$dId]['boards'][] = $board;
                    else $orphans['boards'][] = $board;
                }
            }

            $unassignedComps = [];

            foreach ($equipMap as $id => $e) {
                $type = trim($e['type']);
                if (in_array($type, ['PMT', 'FENICS (Short Cable)', 'FENICS (Long Cable)', 'Active HV Divider (Short Cable)', 'Active HV Divider (Long Cable)'])) {
                    $params = json_decode($e['type_params'], true) ?? [];
                    $ch = $params['phase1_position'] ?? null;
                    $bId = $getParent($id, 'Block');
                    
                    $comp = ['id' => $id, 'type' => $type, 'status' => $e['status'], 'model' => $params['model'] ?? 'N/A', 'qe' => $params['qe'] ?? 'N/A', 'hv' => $params['nominal_hv'] ?? 'N/A', 'channel' => $ch];
                    
                    if ($bId && isset($blocksMap[$bId])) {
                        $blocksMap[$bId]['components'][] = $comp;
                        if ($ch && !$blocksMap[$bId]['channel']) $blocksMap[$bId]['channel'] = $ch;
                    } else {
                        $unassignedComps[] = $comp;
                    }
                }
            }

            foreach ($unassignedComps as $comp) {
                $ch = $comp['channel'];
                $assigned = false;

                if ($ch) {
                    foreach ($blocksMap as $bId => &$b) {
                        if ($b['channel'] == $ch) {
                            $b['components'][] = $comp;
                            $assigned = true;
                            break;
                        }
                    }
                }

                if (!$assigned) {
                    $vId = 'orphan_block_' . ($ch ?? 'X') . '_' . $comp['id'];
                    $blocksMap[$vId] = ['id' => 'N/A', 'type' => 'No PMT Block assigned', 'status' => 'WARNING', 'drawer_id' => null, 'channel' => $ch, 'components' => [$comp]];
                }
            }

            foreach ($blocksMap as $bId => $b) {
                if (!$b['channel']) {
                    foreach ($b['components'] as $c) {
                        if ($c['channel']) { $b['channel'] = $c['channel']; break; }
                    }
                }
                $dId = $b['drawer_id'];
                if ($dId && isset($drawersMap[$dId])) {
                    $drawersMap[$dId]['blocks'][] = $b;
                } else {
                    $orphans['blocks'][] = $b;
                }
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