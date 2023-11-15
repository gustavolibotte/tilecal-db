<?php
namespace App\Library\Loggers;

use Psr\Log\LoggerInterface;

abstract class BaseLogger implements LoggerInterface
{

    public function __construct(){}

    protected function getBaseContext()
    {
        $user = \Config\Services::authenticator()->getUser();
        return [
            'username' => $user->username
        ];
    }

    protected function formatMessage($message, $context=[], bool $addEOL=true)
    {
        $context =array_merge(
            $this->getBaseContext(),
            $context
        );
        $newContext = [];
        foreach($context as $key=>$value){
            $newKey = sprintf('{%s}', $key);
            $newContext[$newKey] = $value;
        }
        if ($addEOL){
            $message .= PHP_EOL;
        }
        return strtr($message, $newContext);
    }

    public function log($level, $message, array $context = array())
    {
        $level = strtolower($level);
        return $this->$level($message, $context);
    }
}