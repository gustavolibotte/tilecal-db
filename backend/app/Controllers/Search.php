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

            $results = [];
            $limitPerCategory = 5;

            $equipModel = model('EquipmentModel');
            $equipments = $equipModel->builder()
                ->select('id, serial, type_name')
                ->groupStart()
                    ->like('id', $query)
                    ->orLike('serial', $query)
                ->groupEnd()
                ->where('deleted_at', null)
                ->limit($limitPerCategory * 2)
                ->get()->getResultArray();

            foreach ($equipments as $eq) {
                $results[] = [
                    'type' => 'equipment',
                    'label' => $eq['id'],
                    'description' => $eq['type_name'] . ($eq['serial'] ? " (Serial: {$eq['serial']})" : ""),
                    'icon' => 'pi-box',
                    'url' => '/equipment/' . $eq['id']
                ];
            }

            $locations = model('LocationModel')->builder()
                ->select('name, description')
                ->like('name', $query)
                ->where('deleted_at', null)
                ->limit($limitPerCategory)
                ->get()->getResultArray();

            foreach ($locations as $loc) {
                $results[] = [
                    'type' => 'location',
                    'label' => $loc['name'],
                    'description' => 'Location: ' . $loc['description'],
                    'icon' => 'pi-map-marker',
                    'url' => '/equipment?location_name=' . urlencode($loc['name'])
                ];
            }

            $institutes = model('InstituteModel')->builder()
                ->select('mne, name')
                ->groupStart()
                    ->like('mne', $query)
                    ->orLike('name', $query)
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

            $statuses = model('StatusModel')->builder()
                ->select('name, description')
                ->like('name', $query)
                ->where('deleted_at', null)
                ->limit($limitPerCategory)
                ->get()->getResultArray();

            foreach ($statuses as $st) {
                $results[] = [
                    'type' => 'status',
                    'label' => $st['name'],
                    'description' => 'Status: ' . $st['description'],
                    'icon' => 'pi-tag',
                    'url' => '/equipment?status_name=' . urlencode($st['name'])
                ];
            }

            return $this->buildAPIResponse(200, $results);

        } catch (Throwable $e) {
            return $this->failInternalError($e);
        }
    }
}