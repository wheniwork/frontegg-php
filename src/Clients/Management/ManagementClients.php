<?php

namespace Frontegg\Clients\Management;

use Frontegg\Authenticator\FronteggAuthenticator;
use Frontegg\Config\Config;
use Frontegg\Entities\Roles\Management\RolesClient;
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
    private ?string $selectedTenantId = null;

    private ?UsersClient $users = null;
    private ?EntitlementsClient $entitlements = null;
    private ?TenantsClient $tenants = null;
    private ?AuditsClient $audits = null;
    private ?RolesClient $roles = null;

    public function __construct(
        Config $config,
        FronteggAuthenticator $authenticator,
        FronteggHttpClient $httpClient,
        ?string $selectedTenantId = null
    ) {
        $this->config = $config;
        $this->authenticator = $authenticator;
        $this->httpClient = $httpClient;
        $this->selectedTenantId = $selectedTenantId;
    }

    public function users(): UsersClient
    {
        if ($this->users === null) {
            $this->users = new UsersClient(
                $this->config,
                $this->authenticator,
                $this->httpClient,
                $this->selectedTenantId
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
                $this->httpClient,
                $this->selectedTenantId
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
                $this->httpClient,
                $this->selectedTenantId
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
                $this->httpClient,
                $this->selectedTenantId
            );
        }
        return $this->audits;
    }

    public function roles(): RolesClient
    {
        if ($this->roles === null) {
            $this->roles = new RolesClient(
                $this->config,
                $this->authenticator,
                $this->httpClient,
                $this->selectedTenantId
            );
        }
        return $this->roles;
    }
}
