<?php

namespace App\Cells;

class MessagesCell extends BaseCell
{
    public function showMessages(){
        helper('message');
        $messages = getMessagesArray();
        if (empty($messages)){
            return '';
        }
        $msgComponents = '';
        foreach ($messages as $msg){
            $msgComponents .= make_message($msg['message'], $msg['type']);
        }
        return $msgComponents;
    }
}
