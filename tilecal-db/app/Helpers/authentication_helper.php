<?php

use Jumbojett\OpenIDConnectClient;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use \App\Entities\User;

function getUser(){
    $session = \Config\Services::session();
        if (! $session->get('isLogged')){
            if (getenv('CI_ENVIRONMENT') == 'local'){
                $userInfo = [
                    'username' => 'anakin.skywalker',
                    'preferredUsername' => 'darth.vader',
                    'firstName' => 'Darth',
                    'lastName' => 'Vader',
                    'name' => 'Darth Vader',
                    'email' => 'darth.vader@cern.ch',
                    'cernId' => 'XXXXXX',
                    'origEmail' => 'anakin.skywalker@cern.ch',
                    'language' => 'EN',
                    'sessionState' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
                    'roles' => ['admin', 'sith_lord', 'the_chosen_one'],
                    'authTime' => time(),
                    'nonce' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'
                ];
            } else {
                $requestClient = \Config\Services::curlrequest();
                $response = $requestClient->get(getenv('SSO_PROVIDER') . '/.well-known/openid-configuration');
                $config = json_decode($response->getBody());

                $oidc = new OpenIDConnectClient(
                    getenv('SSO_PROVIDER'),
                    getenv('SSO_CLIENT_ID'),
                    getenv('SSO_CLIENT_SECRET'),
                    getenv('SSO_ISSUER')
                );
                if (getenv('SSO_REDIRECT_URL') != ''){
                    $oidc->setRedirectURL(getenv('SSO_REDIRECT_URL'));
                }
                $oidc->addScope('openid');
                $oidc->setTimeout(5);
                $oidc->setAllowImplicitFlow(true);
                $oidc->authenticate();

                $response = $requestClient->get($config->jwks_uri);
                $jwks = json_decode($response->getBody(), true);
                $keySet = JWK::parseKeySet($jwks);
                $decodedAccessToken = JWT::decode($oidc->getAccessToken(), $keySet);
                $userInfo = [
                    'username' => $decodedAccessToken->cern_upn,
                    'preferredUsername' => $decodedAccessToken->preferred_username,
                    'firstName' => $decodedAccessToken->given_name,
                    'lastName' => $decodedAccessToken->family_name,
                    'name' => $decodedAccessToken->name,
                    'email' => $decodedAccessToken->email,
                    'cernId' => $decodedAccessToken->cern_person_id,
                    'origEmail' => $decodedAccessToken->cern_mail_upn,
                    'language' => $decodedAccessToken->cern_preferred_language,
                    'sessionState' => $decodedAccessToken->session_state,
                    'roles' => $decodedAccessToken->cern_roles,
                    'authTime' => $decodedAccessToken->auth_time,
                    'nonce' => $decodedAccessToken->nonce
                ];
            }
            setUser($userInfo);
            $currentUser = new User($userInfo);
        }
        else{
            $currentUser = new User($session->get('userInfo'));
        }
        return $currentUser;
}

function setUser(User|array $user)
{
    $session = \Config\Services::session();
    if ($user instanceof User){
        $user = $user->toRawArray();
    }
    $session->set('isLogged', true);
    $session->set('userInfo', $user);
}
