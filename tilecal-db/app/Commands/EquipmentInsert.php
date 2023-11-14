<?php

namespace App\Commands;

use App\Validation\Validators\BatchRegisterValidator;
use App\Validation\Validators\EntryValidator;
use App\Validation\Validators\EquipmentValidator;
use App\Library\Loggers\CLILogger;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

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
    public function run(array $params)
    {
        $filepath = $params[0];
        $typeName = $params[1];
        \Config\Services::authenticator()->setSystem();
        $logger = new CLILogger();
        // $batchValidator = (new BatchRegisterValidator())
        //     ->setFieldValues([
        //         'filepath' => $filepath,
        //         'type_name' => $typeName
        //     ]);
        // if(!$batchValidator->validate()){
        //     CLI::write('Valid input data.' . PHP_EOL);
        //     foreach ($batchValidator->getErrors() as $errMsg){
        //         CLI::write($errMsg . PHP_EOL);
        //     }
        //     return EXIT_USER_INPUT;
        // }
        // $userInput = $batchValidator->getValidatedData();
        $userInput = [
            'filepath' => $filepath,
            'type_name' => $typeName
        ];
        $logger->info('Started insert');
        $equipValidator = EquipmentValidator::getFromTypeName($userInput['type_name']);
        $res = EntryValidator::validateFile(
            $filepath,
            $equipValidator,
            'EquipmentModel',
            $equipValidator->fieldRename,
            $logger
        );
        if ($res){
            $logger->info('Inserted with success');
        } else{
            $logger->critical('Fail');
        }
    }
}
