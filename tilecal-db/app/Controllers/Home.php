<?php

namespace App\Controllers;
use Jumbojett\OpenIDConnectClient;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;


class Home extends BaseController
{
    public function index()
    {
        $client = \Config\Services::curlrequest();
        $response = $client->get(getenv('sso_issuerURL') . '/protocol/openid-connect/certs');
        $jwks = json_decode($response->getBody(), true);
        d($jwks);

        $tokenRequestParams = [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => getenv('sso_clientID'),
                'client_secret' => getenv('sso_clientSecret'),
                'scope' => 'openid'
            ],
            'timeout' => 5,
            'allow_redirects' => true
        ];
        d($tokenRequestParams);

        // try{
        //     $tokenResponse = $client->post(
        //         getenv('sso_issuerURL') . '/protocol/openid-connect/token', 
        //         $tokenRequestParams);
    
        //     d($tokenResponse);
        //     $body = json_decode($tokenResponse->getBody());
        //     $decoded = JWT::decode( $body->access_token, JWK::parseKeySet($jwks));
        //     d($decoded);
        // } catch (Throwable $e) {
        //     echo 'Caught exception: ',  $e->getMessage(), "\n";
        // }

        $oidc = new OpenIDConnectClient(
            getenv('sso_issuerURL'),
            getenv('sso_clientID'),
            getenv('sso_clientSecret'),
        );
        $authentication = $oidc->authenticate();
        d($authentication);
        $oidc->providerConfigParam(array('token_endpoint'=>(getenv('sso_issuerURL') . '/protocol/openid-connect/token')));
        $oidc->addScope('openid');
        $access_token = $oidc->requestClientCredentialsToken()->access_token;
        $clientCredentialsToken = JWT::decode( $access_token, JWK::parseKeySet($jwks));
        d($clientCredentialsToken);
        $name = $oidc->requestUserInfo('name');
        d($name);
        $infos = $oidc->requestUserInfo();
        d($infos);
        $roles = $oidc->requestUserInfo('cern_roles');
        d($roles);

        // $instrospectInfo = $client->request('POST', getenv('sso_issuerURL') . '/protocol/openid-connect/token/introspect', [
        //     'form_params' => [
        //         'token' => $access_token,
        //         'client_id' => getenv('sso_clientID'),
        //         'client_secret' => getenv('sso_clientSecret'),
        //     ],
        //     'timeout' => 5
        // ]);
        // d($instrospectInfo);


        $headerData['pageTitle'] = 'Tilecal Database';
        return view('templates/header', $headerData)
               .view('welcome')
               .view('templates/footer');
    }
}
