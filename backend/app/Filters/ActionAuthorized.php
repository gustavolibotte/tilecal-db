<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ActionAuthorized implements FilterInterface
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

    public function __before__(RequestInterface $request, $arguments = null)
    {
        helper(['app_array', 'response']);
        $action = (is_null($arguments))? null: dot_search_with_default(0, $arguments);
        $user = \Config\Services::user();
        if (!$user->isAuthorized($action)){
            $uri = $request->getUri();
            if (str_starts_with($uri->getPath(), 'api')){
                return failForbidden();
            } else {
                return redirect()->to('forbidden');
            }
        }
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
