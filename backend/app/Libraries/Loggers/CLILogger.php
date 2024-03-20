<?php
namespace App\Library\Loggers;

use Psr\Log\LoggerInterface;
use CodeIgniter\CLI\CLI;

class CLILogger extends BaseLogger
{
    public function emergency($message, $context=[])
    {
        CLI::write(
            $this->formatMessage($message, $context, false),
            'white',
            'red');
    }

    public function alert($message, $context=[])
    {
        CLI::write(
            $this->formatMessage($message, $context, false),
            'red');
    }

    public function critical($message, $context=[])
    {
        CLI::write(
            $this->formatMessage($message, $context, false),
            'red');
    }

    public function error($message, $context=[])
    {
        CLI::write(
            $this->formatMessage($message, $context, false),
            'light_red');
    }

    public function warning($message, $context=[])
    {
        CLI::write(
            $this->formatMessage($message, $context, false),
            'yellow');
    }

    public function notice($message, $context=[])
    {
        CLI::write(
            $this->formatMessage($message, $context, false),
            'yellow');
    }

    public function info($message, $context=[])
    {
        CLI::write(
            $this->formatMessage($message, $context, false),
            'blue');
    }

    public function debug($message, $context=[])
    {
        CLI::write(
            $this->formatMessage($message, $context, false));
    }
}