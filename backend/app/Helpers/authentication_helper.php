<?php

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use CodeIgniter\HTTP\RequestInterface;
use Jumbojett\OpenIDConnectClient;

function tokenAuthorization(RequestInterface $request, $arguments = null)
{   
    $headerValue = $request->header('Authorization')->getValue();
    $accessToken = str_replace('Bearer ', '', $headerValue);
    $requestClient = \Config\Services::curlrequest();
    $ssoConfig = config('SSO');
    $configReponse = $requestClient->get($ssoConfig->configUrl);
    $config = json_decode($configReponse->getBody());
    $keySetResponse = $requestClient->get($config->jwks_uri);
    $jwks = json_decode($keySetResponse->getBody(), true);
    $keySet = JWK::parseKeySet($jwks);
    $decodedAccessToken = JWT::decode($accessToken, $keySet);
    // return buildAPIResponse(
    //     200,
    //     json_decode(json_encode($decodedAccessToken))
    // );
    if ($decodedAccessToken->aud !== $ssoConfig->clientId){
        return failUnauthorized();
    }
    $user = \Config\Services::user();
    $user->setUsername($decodedAccessToken->azp);
    $user->setRoles($decodedAccessToken->cern_roles);
}

function sessionAuthentication(RequestInterface $request, $arguments = null)
{
    $env = \Config\Services::environment();
    $user = \Config\Services::user();
    $session = \Config\Services::session();
    if ($session->get('isLogged')){
        $userInfo = $session->get('userInfo');
        $user->setUsername($userInfo['username']);
        $user->setRoles($userInfo['roles']);
        log_message('info', 'User previously authenticated');
    }else {
        log_message('info', 'User not authenticated');
        if ($env->isLocal()){
            $user->setSystemUser();
            $session->set('isLogged', true);
            $session->set('userInfo', [
                'username' => $user->getUsername(),
                'roles' => $user->getRoles()
            ]);
        } else{
            $userInfo = queryUserForLogin();
            $session->set('isLogged', true);
            $session->set('userInfo', $userInfo);
            $user->setUsername($userInfo['username']);
            $user->setRoles($userInfo['roles']);
        }
        log_message('info', 'User authenticated');
    }
}

function queryUserForLogin(): array
{
    $requestClient = \Config\Services::curlrequest();
    $ssoConfig = config('SSO');
    $response = $requestClient->get($ssoConfig->configUrl);
    $config = json_decode($response->getBody());

    $oidc = new OpenIDConnectClient(
        $ssoConfig->provider,
        $ssoConfig->clientId,
        $ssoConfig->clientSecret,
        $ssoConfig->issuer
    );
    if ($ssoConfig->redirectUrl != ''){
        $oidc->setRedirectURL($ssoConfig->redirectUrl);
    }
    $oidc->addScope(['openid']);
    $oidc->setTimeout(5);
    $oidc->setAllowImplicitFlow(true);
    $oidc->authenticate();

    $response = $requestClient->get($config->jwks_uri);
    $jwks = json_decode($response->getBody(), true);
    $keySet = JWK::parseKeySet($jwks);
    $decodedAccessToken = JWT::decode($oidc->getAccessToken(), $keySet);
    $userInfo = [
        'username' => $decodedAccessToken->cern_upn,
        'roles' => $decodedAccessToken->cern_roles,
    ];
    return $userInfo;
}