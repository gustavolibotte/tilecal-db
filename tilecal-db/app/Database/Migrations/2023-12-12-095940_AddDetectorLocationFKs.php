<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDetectorLocationFKs extends Migration
{
    public function up()
    {
        $this->forge->addForeignKey('detector_location_id', 'detector_locations', 'id');
        $this->forge->processIndexes('equipments_hist');

        $this->forge->addForeignKey('detector_location_id', 'detector_locations', 'id');
        $this->forge->processIndexes('equipments');
    }

    public function down()
    {
        $this->forge->dropForeignKey('equipments_hist', 'equipments_hist_detector_location_id_foreign');
        $this->forge->dropForeignKey('equipments', 'equipments_detector_location_id_foreign');
    }
}
