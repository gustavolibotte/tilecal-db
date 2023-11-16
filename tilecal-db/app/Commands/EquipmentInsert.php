<?php

namespace App\Commands;

use App\Library\Loggers\BaseLogger;
use App\Validation\Validators\BatchRegisterValidator;
use App\Validation\Validators\EntryValidator;
use App\Validation\Validators\EquipmentValidator;
use App\Library\Loggers\CLILogger;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Psr\Log\LoggerInterface;

class EquipmentInsert extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Equipment';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'equipment:insert';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Inserts equipment data from a file';

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
    protected $arguments = [
        'filepath' => 'Path to the file with the data to be inserted.',
        'type_name' => 'Equipment type name'
    ];

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


    protected LoggerInterface $commandLogger;

    public function run(array $params)
    {
        $this->commandSetup();
        $userInput = $this->parseParams($params);
        if ($userInput === EXIT_ERROR){
            return EXIT_ERROR;
        }

        $this->commandLogger->info('Started insert');
        $equipValidator = EquipmentValidator::getFromTypeName($userInput['type_name']);
        $res = EntryValidator::validateFile(
            $userInput['filepath'],
            $equipValidator,
            'EquipmentModel',
            $equipValidator->fieldRename,
            $this->commandLogger
        );
        if ($res){
            $this->commandLogger->info('Inserted with success');
            return EXIT_SUCCESS;
        } else{
            $this->commandLogger->critical('Fail');
            return EXIT_ERROR;
        }
    }

    protected function parseParams(array $params)
    {
        $ruleSet = [
            'filepath' => [
                'rules' => [
                    'required',
                    'local_file_exists',
                    'string_ends_with[.csv]',
                    'local_mime_in[text/csv]'
                ],
                'errors' => [
                    'required' => 'Filepath is required',
                    'string_ends_with' => 'The file must be a csv. (File name did not end in .csv)',
                    'local_file_exists' => 'The given file doesn\'exist',
                    'local_mime_in' => 'The file must be a csv. (File structure not identified as a csv)'
                ]
                ],
            'type_name' => [
                'rules' => [
                    'required',
                    'is_not_unique[equipment_types.name]'
                ],
                'errors' => [
                    'required' => 'Equipment type name is required',
                    'is_not_unique' => 'Equipment type name {value} is not valid.'
                ]
            ]
        ];
        $validator = \Config\Services::validation();
        $validator->setRules($ruleSet);
        $userInput = [
            'filepath' => $params[0],
            'type_name' => $params[1]
        ];
        if (!$validator->run($userInput)){
            $this->commandLogger->error('Errors with input params:');
            foreach($validator->getErrors() as $errorMsg){
                $this->commandLogger->error($errorMsg);
            }
            return EXIT_ERROR;
        }
        return $userInput;
    }

    protected function commandSetup()
    {
        \Config\Services::authenticator()->setSystem();
        $this->commandLogger = new CLILogger();
    }
}
