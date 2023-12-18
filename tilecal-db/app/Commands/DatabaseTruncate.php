<?php

namespace App\Commands;
use CodeIgniter\CLI\CLI;
use Throwable;

class DatabaseTruncate extends CustomCommand
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
    protected $name = 'db:truncate';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Truncates the entire database';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:name';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    protected array|string $rules = [];

    protected array $tables = [
        'equipments_to_files',
        'equipments_to_files_hist',
        'parent_child_equips_hist',
        'parent_child_equips',
        'equipments',
        'equipments_hist',
        'files',
        'files_hist',
        'institutes',
        'institutes_hist',
        'locations',
        'locations_hist',
        'statuses',
        'statuses_hist',
        'allowed_type_slots',
        'allowed_type_slots_hist',
        'type_slots',
        'type_slots_hist',
        'equipment_types',
        'equipment_types_hist',
        'detector_locations',
        'detector_locations_hist',
        'features'
    ];

    protected string $truncateSuccess = 'success';

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $env = env('CI_ENVIRONMENT');
        if (($env !== 'development') && ($env !== 'local')){
            $this->logger->critical('YOU CANNOT TRUNCATE THE PRODUCTION ENVIROMENT.');
            return EXIT_ERROR;
        }
        $msg = sprintf(
            'This action will truncate the whole %s database. Are you sure?',
            env('CI_ENVIRONMENT')
        );
        $userInput = CLI::prompt($msg, ['y', 'n']);
        if ($userInput === 'n'){
            return EXIT_SUCCESS;
        }
        $res = $this->truncateTables();
        if ($res !== $this->truncateSuccess){
            $this->logger->error($res);
            return EXIT_ERROR;
        }
        return EXIT_SUCCESS;
    }

    protected function truncateTables(): string
    {
        $db = db_connect();
        try{
            $db->query('SET FOREIGN_KEY_CHECKS = 0;');
            foreach ($this->tables as $tableName){
                $context['tableName'] = $tableName;
                $queryStr = sprintf('TRUNCATE %s;', $tableName);
                $db->query($queryStr);
                $this->logger->info('Truncated {tableName}.', $context);
            }
        } finally{
            $db->query('SET FOREIGN_KEY_CHECKS = 1;');
        }
        return $this->truncateSuccess;
    }
}
