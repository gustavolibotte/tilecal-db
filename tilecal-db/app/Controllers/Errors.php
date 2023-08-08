<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Errors extends BaseController
{
    public function unauthorized(){
        $errorMsg = 'You are not authorized to do this action, if you think this is an error please contact an admin.';
        $this->response->setStatusCode(401, $errorMsg);
        $viewData = [
            'errorText' => '401 Unauthorized',
            'errorMessage' => $errorMsg,
            'pageTitle' => 'Error 401',
            'pageHeader' => ''
        ];
        return view('errors/http_error', $viewData);
    }
}
