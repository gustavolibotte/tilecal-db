<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Entities\User;
use CodeIgniter\Database\BaseConnection;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    
    protected User $user;
    public $requestTimestamp;
    public BaseConnection $db;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        helper(['view', 'url']);
        $this->user = service('authenticator')->getUser();
        $this->db = db_connect();
        log_message(
            'info',
            '{requestMethod} - {uri}',
            [
                'requestMethod' => strtoupper($this->request->getMethod()),
                'uri' => uri_string()
            ]
            );
    }

    public function buildDataTablesResponse($data)
    {
        $response['draw'] = 1;
        $response['data'] = $data;
        $response['recordsFiltered'] = count($data);
        return json_encode($response);
    }

    protected function checkDeletion(): ?string
    {
        if (!$this->user->isAuthorized('delete')){
            return 'You are not allowed to access deleted items';
        }
        return null;
    }

    public function unauthorized(){
        $errorMsg = 'You are not authorized to do this action. If you think this is an error please contact an admin.';
        $this->response->setStatusCode(401, $errorMsg);
        $viewData = [
            'errorText' => '401 Unauthorized',
            'errorMessage' => $errorMsg,
            'pageTitle' => 'Unauthorized',
            'pageHeader' => ''
        ];
        return default_view('errors/http_error', $viewData);
    }

    public function forbidden(){
        $errorMsg = 'Your current role does not allow you to access this page. If you think this is an error please contact an admin.';
        $this->response->setStatusCode(403, $errorMsg);
        $viewData = [
            'errorText' => '403 Forbidden',
            'errorMessage' => $errorMsg,
            'pageTitle' => 'Forbidden',
            'pageHeader' => ''
        ];
        return default_view('errors/http_error', $viewData);
    }

    public function notFound(?string $extraMsg=null){
        $errorMsg = 'This page was not found, please check if the link is valid. If you think this is an error please contact an admin.';
        if (!is_null($extraMsg)){
            $errorMsg += sprintf(' %s', $extraMsg);
        }
        $this->response->setStatusCode(404, $errorMsg);
        $viewData = [
            'errorText' => '404 Not Found',
            'errorMessage' => $errorMsg,
            'pageTitle' => 'Not Found',
            'pageHeader' => ''
        ];
        return default_view('errors/http_error', $viewData);
    }

    public function deletedEntry()
    {
        $errorMsg = 'This entry was deleted and isn\'t accessible anymore. If you would like for it to be restored please contact an admin.';
        $this->response->setStatusCode(403, $errorMsg);
        $viewData = [
            'errorText' => '403 Deleted',
            'errorMessage' => $errorMsg,
            'pageTitle' => 'Deleted entry',
            'pageHeader' => ''
        ];
        return default_view('errors/http_error', $viewData);
    }

    public function serviceUnavailable()
    {
        $errorMsg = 'This service is currently unavailable. If you think this is an error, please contact an Admin.';
        $this->response->setStatusCode(503, $errorMsg);
        $viewData = [
            'errorText' => '503 Service Unavailable',
            'errorMessage' => $errorMsg,
            'pageTitle' => 'Service Unavailable',
            'pageHeader' => ''
        ];
        return default_view('errors/http_error', $viewData);
    }
}
