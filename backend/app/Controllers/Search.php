<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Search extends APIController
{
    public function index(): ResponseInterface
    {
        try {
            $query = $this->request->getGet('q');
            
            if (empty($query) || strlen($query) < 2) {
                return $this->buildAPIResponse(200, []);
            }

            $sequential = null;
            if (preg_match('/^[A-Za-z0-9]{14,15}$/', $query)) {
                $sequential = substr($query, -7);
            }

            $results = [];
            $limitPerCategory = 5;
            
            $db = db_connect();

            $equipBuilder = $db->table('equipments e')
                ->select('e.id, e.serial, et.name as type_name')
                ->join('equipment_types et', 'e.type_id = et.id', 'left')
                ->groupStart()
                    ->like('e.id', $query, 'both', null, true)
                    ->orLike('e.serial', $query, 'both', null, true)
                    ->orLike('et.name', $query, 'both', null, true);
            
            if ($sequential) {
                $equipBuilder->orLike('e.id', $sequential, 'both', null, true)
                             ->orLike('e.serial', $sequential, 'both', null, true);
            }
            
            $equipments = $equipBuilder->groupEnd()
                ->where('e.deleted_at', null)
                ->orderBy('e.updated_at', 'DESC')
                ->limit($limitPerCategory * 2)
                ->get()->getResultArray();

            foreach ($equipments as $eq) {
                $serialStr = (!empty($eq['serial']) && $eq['serial'] !== $eq['id']) ? " (Serial: {$eq['serial']})" : "";
                $results[] = [
                    'type' => 'equipment',
                    'label' => $eq['id'],
                    'description' => $eq['type_name'] . $serialStr,
                    'icon' => 'pi-box',
                    'url' => '/equipment/' . urlencode($eq['id'])
                ];
            }

            $locations = $db->table('locations')
                ->select('name, description')
                ->groupStart()
                    ->like('name', $query, 'both', null, true)
                    ->orLike('description', $query, 'both', null, true)
                ->groupEnd()
                ->where('deleted_at', null)
                ->limit($limitPerCategory)
                ->get()->getResultArray();

            foreach ($locations as $loc) {
                $results[] = [
                    'type' => 'location',
                    'label' => $loc['name'],
                    'description' => 'Location: ' . ($loc['description'] ?? ''),
                    'icon' => 'pi-map-marker',
                    'url' => '/equipment?location_name=' . urlencode($loc['name'])
                ];
            }

            $institutes = $db->table('institutes')
                ->select('mne, name')
                ->groupStart()
                    ->like('mne', $query, 'both', null, true)
                    ->orLike('name', $query, 'both', null, true)
                ->groupEnd()
                ->where('deleted_at', null)
                ->limit($limitPerCategory)
                ->get()->getResultArray();

            foreach ($institutes as $inst) {
                $results[] = [
                    'type' => 'institute',
                    'label' => $inst['mne'],
                    'description' => 'Institute: ' . $inst['name'],
                    'icon' => 'pi-building',
                    'url' => '/equipment?owner_name=' . urlencode($inst['name'])
                ];
            }

            $statuses = $db->table('statuses')
                ->select('name, description')
                ->groupStart()
                    ->like('name', $query, 'both', null, true)
                    ->orLike('description', $query, 'both', null, true)
                ->groupEnd()
                ->where('deleted_at', null)
                ->limit($limitPerCategory)
                ->get()->getResultArray();

            foreach ($statuses as $st) {
                $results[] = [
                    'type' => 'status',
                    'label' => $st['name'],
                    'description' => 'Status: ' . ($st['description'] ?? ''),
                    'icon' => 'pi-tag',
                    'url' => '/equipment?status_name=' . urlencode($st['name'])
                ];
            }

            return $this->buildAPIResponse(200, $results);

        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 500,
                'message' => 'SQL ERROR: ' . $e->getMessage() . ' at line ' . $e->getLine() . ' in ' . basename($e->getFile()),
                'data' => null
            ]);
        }
    }
}