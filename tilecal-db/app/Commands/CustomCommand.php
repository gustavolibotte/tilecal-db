<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use App\Library\Loggers\CLILogger;
use Psr\Log\LoggerInterface;
use CodeIgniter\CLI\Commands;

class CustomCommand extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'CodeIgniter';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'command:name';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Base class for custom command. Doesn\'t do anything.';

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

    public function __construct(LoggerInterface $logger, Commands $commands)
    {
        parent::__construct($logger, $commands);
        \Config\Services::authenticator()->setSystem();
        $this->logger = new CLILogger();
    }

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $this->logger->info('This is the base class for all custom commands. It does nothing.');
    }

    protected function parseParams(array $userInput, string $ruleSet)
    {
        $validator = \Config\Services::validation();
        if (!$validator->run($userInput, $ruleSet)){
            $this->logger->error('Errors with input params:');
            foreach($validator->getErrors() as $errorMsg){
                $this->logger->error($errorMsg);
            }
            return EXIT_ERROR;
        }
        return $userInput;
    }
}
