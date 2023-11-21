<?php

namespace App\Commands;

use App\Validation\Validators\EntryValidator;
use App\Validation\Validators\EquipmentValidator;

class EquipmentInsert extends CustomCommand
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
    protected $usage = 'command:name [arguments]';

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
        helper('app_array');
        $userInput = [
            'filepath' => dot_search_with_default(0, $params),
            'type_name' => dot_search_with_default(1, $params)
        ];
        $userInput = $this->parseParams($userInput, 'equipCLIParams');
        if ($userInput === EXIT_ERROR){
            return EXIT_ERROR;
        }

        $this->logger->info('Started insert');
        $equipValidator = EquipmentValidator::getFromTypeName($userInput['type_name']);
        $res = model('EquipmentModel')->insertFromFile(
            $userInput['filepath'],
            $equipValidator,
            $this->logger
        );

        if ($res === true){
            $this->logger->info('Inserted with success');
            return EXIT_SUCCESS;
        } else{
            $this->logger->error('Errors found:');
            $loggerContext = ['row' => remove_value('iRow', $res)];
            foreach ($res as $field=>$errMsg){
                $loggerContext['msg'] = $errMsg;
                $loggerContext['field'] = $field;
                $this->logger->error(
                    '{row} {field} {msg}',
                    $loggerContext
                );
            }
            $this->logger->critical('Fail');
            return EXIT_ERROR;
        }
    }
}
