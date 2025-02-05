<?php

namespace Frontegg\Clients\SelfService;

use Frontegg\Authenticator\FronteggAuthenticator;
use Frontegg\Config\Config;
use Frontegg\Http\FronteggHttpClient;
use Frontegg\Entities\Users\SelfService\UsersClient;
use Frontegg\Entities\Entitlements\SelfService\EntitlementsClient;
use Frontegg\Entities\Audits\SelfService\AuditsClient;
use Frontegg\Entities\Events\SelfService\EventsClient;

class SelfServiceClients
{
    private Config $config;
    private FronteggAuthenticator $authenticator;
    private FronteggHttpClient $httpClient;

    private ?UsersClient $users = null;
    private ?EntitlementsClient $entitlements = null;
    private ?AuditsClient $audits = null;
    private ?EventsClient $events = null;

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

    public function events(): EventsClient
    {
        if ($this->events === null) {
            $this->events = new EventsClient(
                $this->config,
                $this->authenticator,
                $this->httpClient
            );
        }
        return $this->events;
    }
}
