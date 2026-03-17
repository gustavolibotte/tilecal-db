<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDaughterboardFields extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('equipment_types');

        $db->transStart();

        $daughterboard = $builder->where('name', 'Daughterboard')->get()->getRowArray();
        
        if ($daughterboard) {
            $params = json_decode($daughterboard['params'], true);
            $newParams = ['fpga_dna_a', 'fpga_dna_b', 'serial_number'];

            foreach ($newParams as $param) {
                if (!in_array($param, $params)) {
                    $params[] = $param;
                }
            }

            $builder->where('name', 'Daughterboard')->update([
                'params' => json_encode($params)
            ]);
        }

        $db->transComplete();
    }

    public function down()
    {
        // pass
    }
}