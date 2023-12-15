<?php

namespace App\Database\Migrations;

use App\Library\BaseMigration;

class FieldFixes extends BaseMigration
{
    public function up()
    {
        $this->upParentChildEquips();
        $this->upDetectorLocationHist();
        $this->upEquipmentTypesHist();
        $this->upInstitutesHist();
    }

    public function down()
    {
        $this->downParentChildEquips();
        $this->downDetectorLocationHist();
        $this->downEquipmentTypesHist();
        $this->downInstitutesHist();
    }

    protected function upInstitutesHist()
    {
        $fields = [
            'mne' => [
                'type' => 'CHAR',
                'constraint' => 3,
                'null' => false,
                'unique' => false
            ]
        ];
        $this->forge->modifyColumn('institutes_hist', $fields);
    }

    protected function upEquipmentTypesHist()
    {
        $fields = [
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => false,
                'unique' => false
            ]
        ];
        $this->forge->modifyColumn('equipment_types_hist', $fields);
    }

    protected function upParentChildEquips()
    {
        $fields = [
            'parent_equip_id' => [
                'type' => 'CHAR',
                'constraint' => 7,
                'null' => false
            ],
            'child_equip_id' => [
                'type' => 'CHAR',
                'constraint' => 7,
                'null' => false
            ]
        ];
        $this->forge->dropForeignKey('parent_child_equips', 'parent_child_equips_child_equip_id_foreign');
        $this->forge->dropForeignKey('parent_child_equips', 'parent_child_equips_parent_equip_id_foreign');
        $this->forge->modifyColumn('parent_child_equips', $fields);
        $this->forge->addForeignKey('child_equip_id', 'equipments', 'id');
        $this->forge->addForeignKey('parent_equip_id', 'equipments', 'id');
        $this->forge->processIndexes('parent_child_equips');

        $this->forge->dropForeignKey('parent_child_equips_hist', 'parent_child_equips_hist_child_equip_id_foreign');
        $this->forge->dropForeignKey('parent_child_equips_hist', 'parent_child_equips_hist_parent_equip_id_foreign');
        $this->forge->modifyColumn('parent_child_equips_hist', $fields);
        $this->forge->addForeignKey('child_equip_id', 'equipments', 'id');
        $this->forge->addForeignKey('parent_equip_id', 'equipments', 'id');
        $this->forge->processIndexes('parent_child_equips_hist');
    }

    protected function upDetectorLocationHist()
    {
        $fields = [
            'name' => [
                'type' => 'CHAR',
                'constraint' => 5,
                'unique' => false,
                'null' => false
            ]
            ];
            $this->forge->modifyColumn('detector_locations_hist', $fields);
    }

    protected function downParentChildEquips()
    {
        $fields = [
            'parent_equip_id' => [
                'type' => 'CHAR',
                'constraint' => 14,
                'null' => false
            ],
            'child_equip_id' => [
                'type' => 'CHAR',
                'constraint' => 14,
                'null' => false
            ]
        ];
        
        $this->forge->dropForeignKey('parent_child_equips', 'parent_child_equips_child_equip_id_foreign');
        $this->forge->dropForeignKey('parent_child_equips', 'parent_child_equips_parent_equip_id_foreign');
        $this->forge->modifyColumn('parent_child_equips', $fields);
        $this->forge->addForeignKey('child_equip_id', 'equipments', 'id');
        $this->forge->addForeignKey('parent_equip_id', 'equipments', 'id');
        $this->forge->processIndexes('parent_child_equips');

        $this->forge->dropForeignKey('parent_child_equips_hist', 'parent_child_equips_hist_child_equip_id_foreign');
        $this->forge->dropForeignKey('parent_child_equips_hist', 'parent_child_equips_hist_parent_equip_id_foreign');
        $this->forge->modifyColumn('parent_child_equips_hist', $fields);
        $this->forge->addForeignKey('child_equip_id', 'equipments', 'id');
        $this->forge->addForeignKey('parent_equip_id', 'equipments', 'id');
        $this->forge->processIndexes('parent_child_equips_hist');
    }

    protected function downDetectorLocationHist()
    {
        $fields = [
            'name' => [
                'type' => 'CHAR',
                'constraint' => 5,
                'unique' => true,
                'null' => false
            ]
            ];
            $this->forge->modifyColumn('detector_locations_hist', $fields);
    }

    protected function downEquipmentTypesHist()
    {
        $fields = [
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => false,
                'unique' => true
            ]
        ];
        $this->forge->modifyColumn('equipment_types_hist', $fields);
    }

    protected function downInstitutesHist()
    {
        $fields = [
            'mne' => [
                'type' => 'CHAR',
                'constraint' => 3,
                'null' => false,
                'unique' => true
            ]
        ];
        $this->forge->modifyColumn('institutes_hist', $fields);
    }
}
