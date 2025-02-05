<?php

namespace Frontegg\Clients\Management;

use Frontegg\Authenticator\FronteggAuthenticator;
use Frontegg\Config\Config;
use Frontegg\Http\FronteggHttpClient;
use Frontegg\Entities\Users\Management\UsersClient;
use Frontegg\Entities\Entitlements\Management\EntitlementsClient;
use Frontegg\Entities\Tenants\Management\TenantsClient;
use Frontegg\Entities\Audits\Management\AuditsClient;

class ManagementClients
{
    private Config $config;
    private FronteggAuthenticator $authenticator;
    private FronteggHttpClient $httpClient;

    private ?UsersClient $users = null;
    private ?EntitlementsClient $entitlements = null;
    private ?TenantsClient $tenants = null;
    private ?AuditsClient $audits = null;

    public function __construct(
        Config $config,
        FronteggAuthenticator $authenticator,
        FronteggHttpClient $httpClient
    ) {
        $this->config = $config;
        $this->authenticator = $authenticator;
        $this->httpClient = $httpClient;
    }

    public function users(): UsersClient
    {
        if ($this->users === null) {
            $this->users = new UsersClient(
                $this->config,
                $this->authenticator,
                $this->httpClient
            );
        }
        return $this->users;
    }

    public function entitlements(): EntitlementsClient
    {
        if ($this->entitlements === null) {
            $this->entitlements = new EntitlementsClient(
                $this->config,
                $this->authenticator,
                $this->httpClient
            );
        }
        return $this->entitlements;
    }

    public function tenants(): TenantsClient
    {
        if ($this->tenants === null) {
            $this->tenants = new TenantsClient(
                $this->config,
                $this->authenticator,
                $this->httpClient
            );
        }
        return $this->tenants;
    }

    public function audits(): AuditsClient
    {
        if ($this->audits === null) {
            $this->audits = new AuditsClient(
                $this->config,
                $this->authenticator,
                $this->httpClient
            );
        }
        return $this->audits;
    }
}
