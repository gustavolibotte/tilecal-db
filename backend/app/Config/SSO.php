<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class SSO extends BaseConfig
{
    /**
     * OIDC Client Id
     * @var string
     */
    public string $clientId;

    /**
     * OIDC Client Secret
     * @var string
     */
    public string $clientSecret;

    /**
     * Auth server URL
     */
    public string $auth;

    /**
     * OIDC SSO Provider URL
     * @var string
     */
    public string $provider;

    /**
     * OIDC SSO Issuer URL
     * @var string
     */
    public string $issuer;

    /**
     * OIDC reguestered redirect URL
     * @var string
     */
    public string $redirectUrl;

    /**
     * OIDC well-known config endpoint url
     */
    public string $configUrl;

    public function __construct()
    {
        $this->clientId = getenv('sso_clientId');
        $this->clientSecret = getenv('sso_clientSecret');
        $this->auth = getenv('sso_auth');
        $this->provider = $this->auth . '/realms/cern';
        $this->issuer = $this->provider;
        $this->redirectUrl = getenv('sso_redirectUrl');
        $this->configUrl = $this->provider . '/.well-known/openid-configuration';
    }
}
