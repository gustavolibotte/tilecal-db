<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ClearValidationLogs extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Housekeeping';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'validationlogs:clear';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'When some input validation generates too many messages, a file is created and outputed to the user. This command clears their server directory.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:name [arguments] [options]';

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

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        helper('filesystem');
        $answer = CLI::prompt('Are you sure you want to delete the logs?', ['y', 'n']);
        if ($answer === 'y'){
            delete_files(config('Paths')->validationLogsDirectory);
            CLI::write('Validation Logs deleted with success.', 'white', 'green');
        } else {
            CLI::write('Validation Logs not deleted.', 'white', 'red');
        }
    }
}
