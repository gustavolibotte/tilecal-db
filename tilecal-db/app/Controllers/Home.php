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
        $response = $client->get(getenv('SSO_ISSUER') . '/protocol/openid-connect/certs');
        $jwks = json_decode($response->getBody(), true);
        d($jwks);

        $tokenRequestParams = [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => getenv('SSO_CLIENT_ID'),
                'client_secret' => getenv('SSO_CLIENT_SECRET'),
                'scope' => 'openid'
            ],
            'timeout' => 5,
            'allow_redirects' => true
        ];
        d($tokenRequestParams);

        // try{
        //     $tokenResponse = $client->post(
        //         getenv('SSO_ISSUER') . '/protocol/openid-connect/token', 
        //         $tokenRequestParams);
    
        //     d($tokenResponse);
        //     $body = json_decode($tokenResponse->getBody());
        //     $decoded = JWT::decode( $body->access_token, JWK::parseKeySet($jwks));
        //     d($decoded);
        // } catch (Throwable $e) {
        //     echo 'Caught exception: ',  $e->getMessage(), "\n";
        // }

        $oidc = new OpenIDConnectClient(
            getenv('SSO_ISSUER'),
            getenv('SSO_CLIENT_ID'),
            getenv('SSO_CLIENT_SECRET'),
        );
        // $authentication = $oidc->authenticate();
        // d($authentication);
        // $oidc->providerConfigParam(array('token_endpoint'=>(getenv('SSO_ISSUER') . '/protocol/openid-connect/token')));
        // $oidc->addScope('openid');
        // $access_token = $oidc->requestClientCredentialsToken()->access_token;
        // $clientCredentialsToken = JWT::decode( $access_token, JWK::parseKeySet($jwks));
        // d($clientCredentialsToken);
        $name = $oidc->requestUserInfo('name');
        d($name);
        $infos = $oidc->requestUserInfo();
        d($infos);
        $roles = $oidc->requestUserInfo('cern_roles');
        d($roles);

        // $instrospectInfo = $client->request('POST', getenv('SSO_ISSUER') . '/protocol/openid-connect/token/introspect', [
        //     'form_params' => [
        //         'token' => $access_token,
        //         'client_id' => getenv('SSO_CLIENT_ID'),
        //         'client_secret' => getenv('SSO_CLIENT_SECRET'),
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
