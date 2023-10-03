<?php

namespace App\Cells;

class MessagesCell extends BaseCell
{
    public function showMessages(){

        $messages = service('messenger')->getMessages();
        if (empty($messages)){
            return '';
        }
        $msgComponents = '';
        foreach ($messages as $msg){
            $msgComponents .= $this->makeMessage($msg['message'], $msg['type']);
        }
        return $msgComponents;
    }

    protected function makeMessage(string $message, string $type): string
    {
        $msg = sprintf('<div class="alert alert-%s alert-dismissible" role="alert">', $type);
        $msg .= sprintf('<div>%s</div>', $message);
        $msg .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        $msg .= '</div>';

        return $msg;
    }
}
