<?php

namespace App\Controllers;
use Jumbojett\OpenIDConnectClient;


class Home extends BaseController
{
    public function index()
    {
        $oidc = new OpenIDConnectClient(
            'https://keycloak-qa.cern.ch/auth/realms/cern',
            getenv('CLIENT_ID'),
            getenv('CLIENT_SECRET'),
        );
        d($oidc);
        // $oidc->setCertPath('/path/to/my.cert');
        $oidc->authenticate();
        $name = $oidc->requestUserInfo('name');
        d($name);
        $headerData['pageTitle'] = 'Tilecal Database';
        return view('templates/header', $headerData)
               .view('welcome')
               .view('templates/footer');
    }
}
