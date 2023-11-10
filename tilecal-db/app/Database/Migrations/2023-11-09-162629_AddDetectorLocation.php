<?php

namespace App\Database\Migrations;
use App\Library\BaseMigration;

class AddDetectorLocation extends BaseMigration
{
    public function up()
    {
        $addFields = ['detector_location_id' => $this->getDefaultFKFieldConfig()];
        $addFields['detector_location_id']['after'] = 'location_id';
        $this->forge->addColumn('equipments', $addFields);

        $detectorLocationFields = [
            'id' => $this->defaultIdField,
            'name' => [
                'type' => 'CHAR',
                'constraint' => 5,
                'unique' => true,
                'null' => false
            ]
        ];
        $detectorLocationFields = $this->addTimestamps($detectorLocationFields);
        $this->forge->addField($detectorLocationFields);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('detector_locations');

        $this->forge->addField($this->getHistFields($detectorLocationFields));
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('detector_locations_hist', true);
    }

    public function down()
    {
        $this->forge->dropTable('detector_locations', true);
        $this->forge->dropTable('detector_locations_hist', true);
        $this->forge->dropColumn('equipments', 'detector_location_id');
    }
}
