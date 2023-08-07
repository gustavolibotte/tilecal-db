<?php

function make_message(string $message, string $type='danger'){
    $alert = sprintf('<div class="alert alert-%s alert-dismissible" role="alert">', $type);
    $alert .= sprintf('<div>%s</div>', $message);
    $alert .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    $alert .= '</div>';
    return $alert;
}


function addMessages(array $messages , string $type='danger'){
    $session = \Config\Services::session();
    $session->set('messages', $messages);
    return true;
}


function getMessagesArray(){
    $session = \Config\Services::session();
    if ($session->has('messages')){
        $messages = $session->get('messages');
        $session->remove('messages');
    } else {
        $messages = [];
    }
    return $messages;
}