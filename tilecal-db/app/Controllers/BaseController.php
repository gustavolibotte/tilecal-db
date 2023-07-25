<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Jumbojett\OpenIDConnectClient;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;


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
    protected $oidc;
    protected $userInfo;
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
    // protected $session;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
        $issuer = getenv('SSO_ISSUER');
        $clientId = getenv('SSO_CLIENT_ID');
        helper(['cookie', 'text']);
        // $this->oidc = new OpenIDConnectClient(
        //     $issuer,
        //     $clientId,
        //     getenv('SSO_CLIENT_SECRET'),
        // );
        // $this->oidc->addScope('openid');
        // $authSessionIDExists = has_cookie('AUTH_SESSION_ID');
        // d($authSessionIDExists);
        // $keycloakSession = has_cookie('KEYCLOAK_SESSION');
        // d($keycloakSession);
        // $keycloakIdentity = has_cookie('KEYCLOAK_IDENTITY');
        // d($keycloakIdentity);

        // $authenticatedOIDC = ($authSessionIDExists)? true: $this->oidc->authenticate();
        // d($authenticatedOIDC);
        // $this->userInfo = $this->oidc->requestUserInfo();
        // d($this->userInfo);

        $requestClient = \Config\Services::curlrequest();
        // $response = $requestClient->get(getenv('SSO_ISSUER') . '/protocol/openid-connect/certs');
        // $jwks = json_decode($response->getBody(), true);
        // d($jwks);

        // $this->oidc->providerConfigParam(array('token_endpoint'=>(getenv('SSO_ISSUER') . '/protocol/openid-connect/token')));
        // $access_token = $this->oidc->requestClientCredentialsToken()->access_token;
        // $clientCredentialsToken = JWT::decode( $access_token, JWK::parseKeySet($jwks));
        // d($clientCredentialsToken);

        $implicitResponse = $requestClient->get(
            $issuer . '/protocol/openid-connect/auth',
            [
                'allow_redirects' => true,
                'query' => [
                    'prompt' => 'none',
                    'response_type' => 'id_token',
                    'client_id' => $clientId,
                    'nonce' => random_string('crypto'),
                    'state' => random_string('crypto'),
                    'scope' => 'openid',
                    'redirect_uri' => base_url(),
                    'audience' => 'authorization-service-api',
                    'response_mode' => 'success'
                ]
            ]
        );
        d($implicitResponse);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    public function buildDataTablesResponse($data)
    {
        $response['draw'] = 1;
        $response['data'] = $data;
        $response['recordsFiltered'] = count($data);
        return json_encode($response);
    }

    public function getHeaderData($pageTitle){
        $headerData['pageTitle'] = $pageTitle;
        return $headerData;
    }

    public function toSnakeCase($input){
        $output = strtolower(str_replace(' ', '_', $input));
        return $output;
    }
}
