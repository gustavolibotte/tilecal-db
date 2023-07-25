<?php

namespace App\Controllers;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;


class Home extends BaseController
{
    public function index()
    {
        $requestClient = \Config\Services::curlrequest();
        $response = $requestClient->get(getenv('SSO_ISSUER') . '/protocol/openid-connect/certs');
        $jwks = json_decode($response->getBody(), true);
        d($jwks);

        // $tokenRequestParams = [
        //     'form_params' => [
        //         'grant_type' => 'user_credentials',
        //         'client_id' => getenv('SSO_CLIENT_ID'),
        //         'client_secret' => getenv('SSO_CLIENT_SECRET'),
        //         'scope' => 'openid'
        //     ],
        //     'timeout' => 5,
        //     'allow_redirects' => true
        // ];

        // try{
        //     $tokenResponse = $requestClient->post(
        //         getenv('SSO_ISSUER') . '/protocol/openid-connect/token', 
        //         $tokenRequestParams);
    
        //     d($tokenResponse);
        //     $body = json_decode($tokenResponse->getBody());
        //     $decoded = JWT::decode( $body->access_token, JWK::parseKeySet($jwks));
        //     d($decoded);
        // } catch (Throwable $e) {
        //     echo 'Caught throwable: ',  $e->getMessage(), "\n";
        // }  catch (Exception $e) {
        //     echo 'Caught exception: ',  $e->getMessage(), "\n";
        // }


        $this->oidc->providerConfigParam(array('token_endpoint'=>(getenv('SSO_ISSUER') . '/protocol/openid-connect/token')));
        $access_token = $this->oidc->requestClientCredentialsToken()->access_token;
        $clientCredentialsToken = JWT::decode( $access_token, JWK::parseKeySet($jwks));
        d($clientCredentialsToken);
        

        // $instrospectInfo = $requestClient->request('POST', getenv('SSO_ISSUER') . '/protocol/openid-connect/token/introspect', [
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
