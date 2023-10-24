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
use CodeIgniter\Model;
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

        helper('view');
        $this->user = service('authenticator')->getUser();
        $this->db = db_connect();
    }

    public function buildDataTablesResponse($data)
    {
        $response['draw'] = 1;
        $response['data'] = $data;
        $response['recordsFiltered'] = count($data);
        return json_encode($response);
    }

    protected function addFilterCondition(string $varName, string $colName, Model $model): Model
    {
        $var = $this->request->getVar($varName);
        if (!empty($var)){
            if (is_array($var)){
                $model = $model->whereIn($colName, $var);
            } else{
                $model = $model->where($colName, $var);
            }
        }
        return $model;
    }

    protected function checkAjaxRequest(): ?string
    {
        if (!$this->request->isAJAX()){
            log_message('warning', 'API received a non AJAX request');
            return 'Requests must be AJAX.';
        }
        return null;
    }

    protected function checkDeletion(): ?string
    {
        if (!$this->user->isAuthorized('delete')){
            return 'You are not allowed to access deleted items';
        }
        return null;
    }
}
