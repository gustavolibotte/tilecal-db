<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBoxPositionToPmt extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Register the new parameter in the 'equipment_types' table
        $builderTypes = $db->table('equipment_types');
        $pmtType = $builderTypes->where('name', 'PMT')->get()->getRowArray();
        
        if ($pmtType) {
            $params = json_decode($pmtType['params'], true) ?? [];
            if (!in_array('box_position', $params)) {
                $params[] = 'box_position';
                $builderTypes->where('name', 'PMT')->update([
                    'params' => json_encode($params)
                ]);
            }
        }

        // 2. Add the 'box_position' key with a null value to existing PMTs
        $builderEquips = $db->table('equipments');
        
        // Exclude soft-deleted PMTs to prevent the 'equipments_hist_update' trigger
        // from throwing a "Column 'action' cannot be null" DatabaseException.
        $pmts = $builderEquips->where('type_id', 1)
                              ->where('deleted_at IS NULL')
                              ->get()
                              ->getResultArray();

        foreach ($pmts as $pmt) {
            $typeParams = json_decode($pmt['type_params'], true) ?? [];
            
            if (!array_key_exists('box_position', $typeParams)) {
                $typeParams['box_position'] = null;
                
                $builderEquips->where('id', $pmt['id'])
                              ->update(['type_params' => json_encode($typeParams)]);
            }
        }

        $db->transComplete();
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Revert 'equipment_types' table modifications
        $builderTypes = $db->table('equipment_types');
        $pmtType = $builderTypes->where('name', 'PMT')->get()->getRowArray();
        
        if ($pmtType) {
            $params = json_decode($pmtType['params'], true) ?? [];
            $index = array_search('box_position', $params);
            
            if ($index !== false) {
                unset($params[$index]);
                $builderTypes->where('name', 'PMT')->update([
                    'params' => json_encode(array_values($params)) // Reindex to keep it a JSON array
                ]);
            }
        }

        // 2. Revert 'equipments' table modifications
        $builderEquips = $db->table('equipments');
        $pmts = $builderEquips->where('type_id', 1)
                              ->where('deleted_at IS NULL')
                              ->get()
                              ->getResultArray();

        foreach ($pmts as $pmt) {
            $typeParams = json_decode($pmt['type_params'], true) ?? [];
            
            if (array_key_exists('box_position', $typeParams)) {
                unset($typeParams['box_position']);
                
                $builderEquips->where('id', $pmt['id'])
                              ->update(['type_params' => json_encode($typeParams)]);
            }
        }

        $db->transComplete();
    }
}