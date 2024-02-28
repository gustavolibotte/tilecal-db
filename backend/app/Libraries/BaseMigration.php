<?php

namespace App\Library;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\Forge;

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

    protected BaseConnection $dbconnect;

    public function __construct(?Forge $forge = null)
    {
        parent::__construct($forge);
        $user = \Config\Services::user();
        $user->setSystemUser();
        $this->dbconnect = db_connect();
    }

    protected function addTimestamps(array $tableFields): array
    {
        return array_merge($tableFields, $this->defaultTimestampFields);
    }

    protected function getHistFields(array $tableFields): array
    {
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
        foreach($tableFields as &$fieldInfo){
            remove_value('unique', $fieldInfo, false);
        }
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