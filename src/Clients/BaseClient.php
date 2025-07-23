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
    protected ?string $selectedTenantId = null;

    public function __construct(
        Config $config,
        IdentityManager $identityManager,
        FronteggHttpClient $httpClient,
        ?string $selectedTenantId = null
    ) {
        $this->config = $config;
        $this->identityManager = $identityManager;
        $this->httpClient = $httpClient;
        $this->selectedTenantId = $selectedTenantId;
    }

    /**
     * Get the selected tenant ID if available
     *
     * @return string|null The selected tenant ID or null if none is set
     */
    protected function getSelectedTenantId(): ?string
    {
        return $this->selectedTenantId;
    }

    /**
     * Get the tenant ID to use - either the provided one or the selected one
     *
     * @param string|null $tenantId The explicitly provided tenant ID
     * @return string The tenant ID to use
     * @throws \InvalidArgumentException If no tenant ID is provided and none is selected
     */
    protected function resolveTenantId(?string $tenantId = null): string
    {
        if ($tenantId !== null) {
            return $tenantId;
        }

        if ($this->selectedTenantId !== null) {
            return $this->selectedTenantId;
        }

        throw new \InvalidArgumentException('No tenant ID provided and no tenant is currently selected. Use selectTenant() or provide a tenant ID explicitly.');
    }

    /**
     * Check if a tenant is currently selected
     *
     * @return bool True if a tenant is selected, false otherwise
     */
    protected function hasSelectedTenant(): bool
    {
        return $this->selectedTenantId !== null;
    }
}
