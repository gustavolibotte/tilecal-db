<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateLongCableEquipments extends Migration
{
    /**
     * Migrates specific equipment instances to their "Long Cable" types
     * based on IDs provided in JSON data files.
     */
    public function up()
    {
        $fenicsFile = ROOTPATH . 'data/long_cable_fenics_ids.json';
        $hvFile     = ROOTPATH . 'data/long_cable_hv_ids.json';

        // 1. Migrate FENICS (ID 4 -> ID 40)
        if (file_exists($fenicsFile)) {
            $jsonContent = file_get_contents($fenicsFile);
            $fenicsIds = json_decode($jsonContent, true);

            if (!empty($fenicsIds) && is_array($fenicsIds)) {
                // Update only equipments that are currently FENICS (Standard) and are in the list
                $this->db->table('equipments')
                         ->where('type_id', 4) // Ensure we only touch standard FENICS
                         ->whereIn('id', $fenicsIds)
                         ->update(['type_id' => 40]);
                         
                // Note: Any FENICS NOT in this list remains type_id 4 (Short Cable)
            }
        }

        // 2. Migrate Active HV Dividers (ID 3 -> ID 41)
        if (file_exists($hvFile)) {
            $jsonContent = file_get_contents($hvFile);
            $hvIds = json_decode($jsonContent, true);

            if (!empty($hvIds) && is_array($hvIds)) {
                // Update only equipments that are currently HV Divider (Standard) and are in the list
                $this->db->table('equipments')
                         ->where('type_id', 3) // Ensure we only touch standard HV Dividers
                         ->whereIn('id', $hvIds)
                         ->update(['type_id' => 41]);
                         
                // Note: Any HV Divider NOT in this list remains type_id 3 (Short Cable)
            }
        }

        // PMT Blocks and Mini Drawers are deliberately skipped here 
        // as their specific IDs are not yet known/provided.
    }

    /**
     * Reverts the data migration.
     * Moves equipments back to their generic/standard types.
     */
    public function down()
    {
        // 1. Move FENICS (Long Cable) back to FENICS (Standard)
        $this->db->table('equipments')
                 ->where('type_id', 40)
                 ->update(['type_id' => 4]);

        // 2. Move Active HV Divider (Long Cable) back to Active HV Divider (Standard)
        $this->db->table('equipments')
                 ->where('type_id', 41)
                 ->update(['type_id' => 3]);
    }
}