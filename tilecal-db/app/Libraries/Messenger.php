<?php

namespace App\Library;

class Messenger{

    private array $messages = [];
    static array $validTypes = [
        'danger',
        'success'
    ];

    public function addMessage(string $message, string $type)
    {
        $this->messages[] = [
            'message' => $message,
            'type' => $type
        ];
    }

    public function addMessages(array $params)
    {
        foreach ($params as $param){
            $this->addMessage($param['message'], $param['type']);
        }
    }

    public function dumpMessages()
    {
        $session = \Config\Services::session();
        $session->set('messages', $this->messages);
    }

    public function getMessages(): ?array
    {
        $session = \Config\Services::session();
        $messages = $session->get('messages');
        $session->remove('messages');
        return $messages;
    }

    public function addDeleteSuccess()
    {
        $this->addMessage('Deleted with success', 'success');
    }

    public function addRestoreSuccess()
    {
        $this->addMessage('Restored with success', 'success');
    }

    public function addRegisterSuccess()
    {
        $this->addMessage('Registered with success', 'success');
    }
}
