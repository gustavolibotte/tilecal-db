<?php

namespace App\Commands;

class ClearSession extends CustomCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Session';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'sess:clear';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Clears all the active app sessions';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'sess:clear';

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
        $sessionConfig = config('Session');
        if (str_ends_with($sessionConfig->driver, 'DatabaseHandler')){
            $db = db_connect();
            $db->query('TRUNCATE ci_sessions;');
        } else{
            $this->logger->error(
                'Can\'t clear session with {driver} driver.',
                ['driver' => $sessionConfig->driver]
            );
            return EXIT_ERROR;
        }
        $this->logger->info('Session Cleared');
        return EXIT_SUCCESS;
    }
}
