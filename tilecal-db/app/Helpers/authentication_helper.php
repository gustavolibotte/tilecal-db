<?php
use Jumbojett\OpenIDConnectClient;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;

function getUser(){
    $session = \Config\Services::session();
        if (! $session->get('isLogged')){
            if (getenv('CI_ENVIRONMENT') == 'local'){
                $userInfo = [
                    'username' => 'anakin.skywalker',
                    'preferredUsername' => 'darth.vader',
                    'firstName' => 'Darth',
                    'lastName' => 'vader',
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
            $tempTime = 20*60;
            $session->setTempdata('isLogged', true, $tempTime);
            $session->setTempdata('userInfo', $userInfo, $tempTime);
            $currentUser = new \App\Entities\User($userInfo);
        }
        else{
            $currentUser = new \App\Entities\User($session->get('userInfo'));
        }
        return $currentUser;
}
