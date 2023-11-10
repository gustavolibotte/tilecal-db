<?php

namespace App\Library;

use CodeIgniter\Database\Migration;

abstract class BaseMigration extends Migration{

    public array $defaultIdField = [
        'type' => 'INT',
        'auto_increment' => true,
        'unsigned' => true
    ];

    public array $defaultTimestampFields = [
        'created_at' => [
            'type' => 'TIMESTAMP',
            'constraint' => 6,
            'null' => false
        ],
        'updated_at' => [
            'type' => 'TIMESTAMP',
            'constraint' => 6,
            'null' => false
        ],
        'deleted_at' => [
            'type' => 'TIMESTAMP',
            'constraint' => 6,
            'null' => true
        ]
    ];

    public array $equipmentIdFieldConfig = [
        'type'=> 'CHAR',
        'constraint' => 7,
        'null' => false
    ];

    protected function addTimestamps(array $tableFields): array
    {
        return array_merge($tableFields, $this->defaultTimestampFields);
    }

    protected function getHistFields(array $tableFields): array
    {
        echo "Create Hist\n";
        helper('app_array');
        $recordId = remove_value('id', $tableFields);
        remove_value('auto_increment', $recordId, false);
        $baseFields = [
            'id' => $this->defaultIdField,
            'user' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false
            ],
            'action' => [
                'type' => 'ENUM',
                'constraint' => ['insert', 'update', 'delete', 'restore'],
                'null' => false
            ],
            'record_id' => $recordId,
        ];
        remove_values(['updated_at', 'deleted_at'], $tableFields);
        $fields = array_merge($baseFields, $tableFields);
        return $fields;
    }

    public function getDefaultFKFieldConfig(bool $nullable=true): array
    {
        $defaultFKFieldConfig = [
            'type' => 'INT',
            'unsigned' => true,
            'null' => $nullable
        ];
        return $defaultFKFieldConfig;
    }
}