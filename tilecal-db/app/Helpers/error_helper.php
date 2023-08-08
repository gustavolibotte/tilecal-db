<?php

function unauthorizedError($response){

    $errorMsg = 'This action is not authorized, if you think this is an error please contact an admin.';
    $response->setStatusCode(401, $errorMsg);
    $viewData = [
        'errorText' => '401 Unauthorized',
        'errorMessage' => $errorMsg,
        'pageTitle' => 'Error 401',
        'pageHeader' => ''
    ];
    return view('errors/http_error', $viewData);
}