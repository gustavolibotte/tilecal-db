<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Errors extends BaseController
{
    public function unauthorized(){
        $errorMsg = 'You are not authorized to do this action. If you think this is an error please contact an admin.';
        $this->response->setStatusCode(401, $errorMsg);
        $viewData = [
            'errorText' => '401 Unauthorized',
            'errorMessage' => $errorMsg,
            'pageTitle' => 'Unauthorized',
            'pageHeader' => ''
        ];
        return default_view('errors/http_error', $viewData);
    }

    public function notFound(){
        $errorMsg = 'This page was not found, please check if the link is valid. If you think this is an error please contact an admin.';
        $this->response->setStatusCode(404, $errorMsg);
        $viewData = [
            'errorText' => '404 Not Found',
            'errorMessage' => $errorMsg,
            'pageTitle' => 'Not Found',
            'pageHeader' => ''
        ];
        return default_view('errors/http_error', $viewData);
    }
}
