<?php

function make_message(string $message, string $type='danger'){
    $alert = sprintf('<div class="alert alert-%s alert-dismissible" role="alert">', $type);
    $alert .= sprintf('<div>%s</div>', $message);
    $alert .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    $alert .= '</div>';
    return $alert;
}
