<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

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
        } catch (Throwable $e){
            return failInternalError($e);
        }
    }

    protected function __before__(RequestInterface $request, $arguments = null)
    {
        helper('response');
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
            return failBadRequest(
                message: 'No Aauthorization header'
            );
        }

        $value = $authorizationHeader->getValue();
        if (!str_starts_with($value, 'Bearer ')) {
            return failBadRequest(
                message: 'No Bearer token'
            );
        }
        $token = str_replace('Bearer ', '', $value);
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
