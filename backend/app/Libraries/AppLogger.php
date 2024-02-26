<?php
namespace App\Library;

use CodeIgniter\Log\Logger;


/**
 * Extends CodeIgniter base Logger class for better log messages. See log method.
 */
class AppLogger extends Logger
{
    /**
     * Logs with an arbitrary level.
     * The message structure is build as the following:
     * {requestTimestamp} - {username} - {file} - {line} - {msg}
     * requestTimestamp is the timestamp when the request was received fromthe client
     * username: current username from the user session
     * file: file that called log_message
     * line: code line that called log_message
     * msg: message to log
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function log($level, $message, array $context = []): bool
    {
        $requestTimestamp = service('Time')->getRequestTimestamp();
        $user = \Config\Services::user();
        // isSet is called to avoid loging problems when calling the session
        // inside the authentication filter
        if (array_key_exists('username', $context)){
            $username = $context['username'];
        } elseif($user->isSet()) {
            $username = \Config\Services::user()->getUsername();;
        } else {
            $username = null;
        }
        // Adding placeholders for line and file that will be handled by parent::log
        $message = sprintf(
            '%s;%s;{file};{line};%s',
            $requestTimestamp->format('Y-m-d H:i:s.u'),
            $username,
            $message
        );
        $res = parent::log($level, $message, $context);
        return $res;
    }
}