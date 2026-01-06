<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateComponentVariationsTypes extends Migration
{
    /**
     * Applies structural changes to the equipment_types table.
     * 1. Renames generic types to "Short Cable" or "Standard".
     * 2. Inserts new variation types (Long Cable/Patch-Panel) using specific IDs.
     */
    public function up()
    {
        // ---------------------------------------------------------
        // Part 1: Rename Existing Types
        // ---------------------------------------------------------

        // ID 3: Active HV Divider -> Active HV Divider (Short Cable)
        $this->db->query("UPDATE equipment_types SET name = 'Active HV Divider (Short Cable)', description = 'Standard Active HV Divider with short cable' WHERE id = 3");

        // ID 4: FENICS -> FENICS (Short Cable)
        $this->db->query("UPDATE equipment_types SET name = 'FENICS (Short Cable)', description = 'Standard FENICS with short cable' WHERE id = 4");

        // ID 5: PMT Block -> PMT Block (Standard)
        $this->db->query("UPDATE equipment_types SET name = 'PMT Block (Standard)', description = 'Standard PMT Block Assembly (Short Cables)' WHERE id = 5");

        // ID 8: Mini Drawer -> Mini Drawer (Standard)
        $this->db->query("UPDATE equipment_types SET name = 'Mini Drawer (Standard)', description = 'Legacy/Standard Mini Drawer' WHERE id = 8");

        // ---------------------------------------------------------
        // Part 2: Create New Variations
        // ---------------------------------------------------------
        // We force specific IDs (40, 41, 42, 43) to match the proposed schema.
        
        $data = [
            [
                'id'          => 40,
                'name'        => 'FENICS (Long Cable)',
                'in_detector' => 1,
                'project'     => '20',
                'system'      => 'LL',
                'id_regex'    => '/02[0-9]{5}/',
                'params'      => '[]',
                'active'      => 1,
                'description' => 'FENICS variation with long cable',
                'user'        => 'migration'
            ],
            [
                'id'          => 41,
                'name'        => 'Active HV Divider (Long Cable)',
                'in_detector' => 1,
                'project'     => '20',
                'system'      => 'LL',
                'id_regex'    => '/00[0-9]{5}/',
                'params'      => '[]',
                'active'      => 1,
                'description' => 'Active HV Divider variation with long cable',
                'user'        => 'migration'
            ],
            [
                'id'          => 42,
                'name'        => 'PMT Block (Long Cable)',
                'in_detector' => 1,
                'project'     => '20',
                'system'      => 'LO',
                'id_regex'    => '/[A-Z0-9]{7}/',
                'params'      => '[]',
                'active'      => 1,
                'description' => 'PMT Block Assembly for Patch-Panel Drawers (Long Cables)',
                'user'        => 'migration'
            ],
            [
                'id'          => 43,
                'name'        => 'Mini Drawer (With Patch-Panel)',
                'in_detector' => 1,
                'project'     => '20',
                'system'      => 'LL',
                'id_regex'    => '/2[0-4](00[1-9]|0[1-9][0-9]|1[0-9][0-9]|2[0-6][0-9]|27[0-2])[0-1][0-5]/',
                'params'      => '[]',
                'active'      => 1,
                'description' => 'New Mini Drawer with Patch-Panel (Requires Long Cables)',
                'user'        => 'migration'
            ]
        ];

        // Using insertBatch to insert multiple rows efficiently
        $this->db->table('equipment_types')->insertBatch($data);
    }

    /**
     * Reverts the changes.
     */
    public function down()
    {
        // 1. Delete the new variations
        $this->db->table('equipment_types')->whereIn('id', [40, 41, 42, 43])->delete();

        // 2. Revert names to original generic versions
        $this->db->query("UPDATE equipment_types SET name = 'Active HV Divider', description = NULL WHERE id = 3");
        $this->db->query("UPDATE equipment_types SET name = 'FENICS', description = NULL WHERE id = 4");
        $this->db->query("UPDATE equipment_types SET name = 'PMT Block', description = NULL WHERE id = 5");
        $this->db->query("UPDATE equipment_types SET name = 'Mini Drawer', description = NULL WHERE id = 8");
    }
}