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
        $response = $client->get(getenv('OPENID_PROVIDER') . '/protocol/openid-connect/certs');
        $jwks = json_decode($response->getBody(), true);
        d($jwks);

        $tokenRequestParams = [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => getenv('CLIENT_ID'),
                'client_secret' => getenv('CLIENT_SECRET'),
                'scope' => 'openid'
            ],
            'timeout' => 5,
            'allow_redirects' => true
        ];
        d($tokenRequestParams);

        $authenticationParams = [
            'allow_redirects' => true,
            'query' => [
                'redirect_uri' => base_url(),
                'scope' => 'open_id',
                'response_type' => 'code'
            ]
        ];
        $authResponse = $client->get(
            getenv('OPENID_PROVIDER') . '/protocol/openid-connect/auth',
            $authenticationParams
        );
        d($authResponse);

        $tokenResponse = $client->post(
            getenv('OPENID_PROVIDER') . '/protocol/openid-connect/token', 
            $tokenRequestParams);

        d($tokenResponse);
        $body = json_decode($tokenResponse->getBody());
        $decoded = JWT::decode( $body->access_token, JWK::parseKeySet($jwks));
        d($decoded);

        $oidc = new OpenIDConnectClient(
            getenv('OPENID_PROVIDER'),
            getenv('CLIENT_ID'),
            getenv('CLIENT_SECRET'),
        );
        $authentication = $oidc->authenticate();
        d($authentication);
        $oidc->providerConfigParam(array('token_endpoint'=>(getenv('OPENID_PROVIDER') . '/protocol/openid-connect/token')));
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

        $instrospectInfo = $client->request('POST', getenv('OPENID_PROVIDER') . '/protocol/openid-connect/token/introspect', [
            'form_params' => [
                'token' => $access_token,
                'client_id' => getenv('CLIENT_ID'),
                'client_secret' => getenv('CLIENT_SECRET'),
            ],
            'timeout' => 5
        ]);
        d($instrospectInfo);


        $headerData['pageTitle'] = 'Tilecal Database';
        return view('templates/header', $headerData)
               .view('welcome')
               .view('templates/footer');
    }
}
