<?php

namespace App\Cells;

class MessagesCell extends BaseCell
{
    public function showMessages(){
        helper('message');
        $session = \Config\Services::session();
        $messages = $session->getFlashdata('messages');
        if (is_null($messages)){
            return '';
        }
        $components = '';
        foreach ($messages as $msg){
            $components .= make_message($msg['message'], $msg['type']);
        }
        return $components;
    }
}
