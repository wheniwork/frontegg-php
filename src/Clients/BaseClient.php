<?php

namespace Frontegg\Clients;

use Frontegg\Config\Config;
use Frontegg\Http\FronteggHttpClient;
use Frontegg\Identity\IdentityManager;

abstract class BaseClient
{
    protected Config $config;
    protected IdentityManager $identityManager;
    protected FronteggHttpClient $httpClient;

    public function __construct(
        Config $config,
        IdentityManager $identityManager,
        FronteggHttpClient $httpClient
    ) {
        $this->config = $config;
        $this->identityManager = $identityManager;
        $this->httpClient = $httpClient;
    }
}
