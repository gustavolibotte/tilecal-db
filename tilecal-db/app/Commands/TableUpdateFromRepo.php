<?php

namespace App\Commands;

class TableUpdateFromRepo extends CustomCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Database';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'db:repo';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Updates specific tables from the data inside the repo.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:name [arguments]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = ['data', 'unique_column'];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    protected array|string $rules = [
        'data' => [
            'rules' => [
                'in_list[institutes,statuses,locations,features,equipment_types]'
            ],
            'errors' => [
                'in_list' => 'data must be a value inside {params}'
            ]
        ],
        'unique_column' => [
            'rules' => [
                'is_valid_column[{data}]'
            ],
            'errors' => [
                'Column {value} doesn\'t exist in {param}.'
            ]
        ]
    ];

    protected array $dataModelMap = [
        'institutes' => 'InstituteModel',
        'statuses' => 'StatusModel',
        'locations' => 'LocationModel',
        'features' => 'FeatureModel',
        'equipment_types' => 'EquipmentTypeModel'
    ];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $userInput = $this->parseParams($params);
        if ($userInput === EXIT_ERROR){
            return EXIT_ERROR;
        }

        helper(['json', 'path']);
        $filepath = joinPaths([
            config('Paths')->dataDirectory,
            sprintf('%s.json', $userInput['data'])
        ]);
        $repoData = readJson($filepath, true);
        $dataModel = model($this->dataModelMap[$userInput['data']]);
        foreach ($repoData as $data){
            $field = $userInput['unique_column'];
            $entity = $dataModel
                ->where($field, $data[$field])
                ->first();
            if (isset($entity)){
                $this->logger->info(sprintf(
                    'Data identified by %s: %s already exists. Continuing.',
                    $field, $data[$field]
                ));
                continue;
            }
            $this->logger->info(sprintf(
                'Data identified by %s: %s not found. Inserting.',
                $field, $data[$field]
            ));
            $newEntity = new $dataModel->returnType($data);
            $dataModel->insert($newEntity);
        }
    }
}
