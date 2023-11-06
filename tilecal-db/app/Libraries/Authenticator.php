<?php

namespace App\Library;
use Jumbojett\OpenIDConnectClient;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use \App\Entities\User;

class Authenticator{

    private bool $isMigration;
    private ?User $user;

    public function __construct(?bool $isMigration=false)
    {
        $this->isMigration = is_null($isMigration)? false: $isMigration;
        $this->user = null;
    }

    public function getUser(): User
    {   
        if ($this->requestIsAuthenticated()){
            return $this->user;
        }
        $session = \Config\Services::session();
        if($session->get('isLogged')){
            $this->user = new User($session->get('userInfo'));
            log_message('info', 'User previously authenticated');
            return $this->user;
        }
        log_message('info', 'User not authenticated');
        $this->user = $this->authenticate();
        log_message('info', 'User authenticated');
        return $this->user;
    }

    public function requestIsAuthenticated(): bool
    {
        return !is_null($this->user);
    }

    private function authenticate(): User
    {
        if($this->isMigration){
            log_message('info', 'Generating migration logging info.');
            $userInfo = [
                'username' => 'tilep2db',
                'preferredUsername' => 'tile.phase2db',
                'firstName' => 'System',
                'lastName' => 'User',
                'name' => 'System User',
                'email' => 'tile.phase2db@cern.ch',
                'cernId' => 'XXXXXX',
                'origEmail' => 'tile.phase2db@cern.ch',
                'language' => 'EN',
                'sessionState' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
                'roles' => ['admin'],
                'authTime' => time(),
                'nonce' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'
            ];
        } elseif(getenv('CI_ENVIRONMENT') == 'local'){
            log_message('info', 'Generating local logging info.');
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
        } else{
            log_message('info', 'Logging using SSO with implicit flow.');
            $userInfo = $this->implicitFlow();
        }
        $session = service('session');
        $session->set('isLogged', true);
        $session->set('userInfo', $userInfo);
        return new User($userInfo);
    }

    private function implicitFlow(): array
    {
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
        return $userInfo;
    }

    public function setMigration(): self
    {
        $this->isMigration = true;
        return $this;
    }

    public function unsetMigration(): self
    {
        $this->isMigration = false;
        return $this;
    }

    // /**
    //  * Custom log_message function fot the authenticator.
    //  * The log_message retreives the user from the authenticator service. If the service
    //  * used log_message function without special conditions it could fall into
    //  * an infinite recursion. This method ovewrites the username with null in when the
    //  * user is not authenticated to avoid this scenarios.
    //  *
    //  * @param string $level Log level
    //  * @param string $message Log message
    //  * 
    //  * @return mixed return of log_message 
    //  */
    // private function log_message(string $level, string $message): mixed
    // {
    //     $username = is_null($this->user)? null: $this->user->username;
    //     return log_message(
    //         $level,
    //         $message,
    //         ['username' => $username]
    //     );
    // }
}
