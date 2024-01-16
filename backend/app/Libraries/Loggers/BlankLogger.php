<?php
namespace App\Library\Loggers;

class BlankLogger extends BaseLogger
{
    public function emergency($message, $context=[]){}

    public function alert($message, $context=[]){}

    public function critical($message, $context=[]){}

    public function error($message, $context=[]){}

    public function warning($message, $context=[]){}

    public function notice($message, $context=[]){}

    public function info($message, $context=[]){}

    public function debug($message, $context=[]){}
}