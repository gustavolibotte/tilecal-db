<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;

class APIAuthentication implements FilterInterface
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
        helper('response');
        try{
            return $this->__before__($request, $arguments);
        } catch (\Throwable $e){
            $env = \Config\Services::environment();
            if ($env->isLocal()){
                return failInternalError($e);
            } else {
                return failUnauthorized();
            }
        }
    }

    public function __before__(RequestInterface $request, $arguments = null)
    {
        $env = \Config\Services::environment();
        $user = \Config\Services::user();
        if ($env->isLocal() && $request->hasHeader('TestUser')){
            $user->setSystemUser();
            return;
        }

        $contentTypeHeader = $request->header('Content-Type');
        if (empty($contentTypeHeader)){
            return failBadRequest(
                message: 'No Content-Type Header'
            );
        }

        if ($contentTypeHeader->getValue() !== 'application/json'){
            return failBadRequest(
                message: 'Content-Type Header is not json'
            );
        }

        $requestedWithHeader = $request->header('X-Requested-With');
        if (empty($requestedWithHeader)){
            return failBadRequest(
                message: 'No X-Requested-With Header'
            );
        }

        if ($requestedWithHeader->getValue() !== 'XMLHttpRequest'){
            return failBadRequest(
                message: 'X-Requested-With Header is not XMLHttpRequest'
            );
        }

        $authorizationHeader = $request->header('Authorization');
        if (empty($authorizationHeader)) {
            return failUnauthorized();
        }

        $headerValue = $authorizationHeader->getValue();
        if (!str_starts_with($headerValue, 'Bearer ')) {
            return failUnauthorized();
        }

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
