<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Jumbojett\OpenIDConnectClient;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;

class Authentication implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = \Config\Services::session();
        if ($session->get('isLogged')){
            return $request;
        } elseif (getenv('CI_ENVIRONMENT') == 'local'){
            $sessionData = [
                'isLogged' => true,
                'userInfo' => [
                    'name' => 'local',
                    'roles' => ['admin']
                ]
            ];
        } else{
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
            d($decodedAccessToken);
            $sessionData = [
                'isLogged' => true,
            ];
        }
        $session->setTempdata($sessionData, ttl: 20*60);
        return $request;
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
