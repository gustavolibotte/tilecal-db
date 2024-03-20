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

    protected array|string $rules = [];

    public function __construct(LoggerInterface $logger, Commands $commands)
    {
        parent::__construct($logger, $commands);
        \Config\Services::user()->setSystemUser();
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

    protected function parseParams($params)
    {
        $userInput = $this->getUserInput($params);
        $validator = \Config\Services::validation();
        if (is_array($this->rules)){
            $validator->setRules($this->rules);
            $valid = $validator->run($userInput);
        } else{
            $valid = $validator->run($userInput, $this->rules);
        }
        if (!$valid){
            $this->logger->error('Errors with input params:');
            foreach($validator->getErrors() as $errorMsg){
                $this->logger->error($errorMsg);
            }
            return EXIT_ERROR;
        }
        return $userInput;
    }

    protected function getUserInput($params)
    {
        helper('app_array');
        $userInput = [];
        foreach ($this->arguments as $i=>$paramName){
            $userInput[$paramName] = dot_search_with_default($i, $params);
        }
        return $userInput;
    }
}
