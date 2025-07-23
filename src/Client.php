<?php

namespace Frontegg;

use Frontegg\Config\Config;
use Frontegg\Exception\Identity\TokenValidationException;
use Frontegg\Http\FronteggHttpClient;
use Frontegg\Clients\Management\ManagementClients;
use Frontegg\Clients\SelfService\SelfServiceClients;
use Frontegg\Identity\IdentityManager;
use Frontegg\Identity\Claims\UserTokenClaims;
use Frontegg\Identity\Claims\TenantTokenClaims;
use Frontegg\Exception\UnauthorizedException;
use Frontegg\Exception\Identity\NoTokenException;
use Frontegg\Exception\Identity\InvalidTokenException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Main Frontegg client class with authentication capabilities
 */
class Client
{
    private Config $config;
    private IdentityManager $identityManager;
    private FronteggHttpClient $httpClient;
    private ?ManagementClients $management = null;
    private ?SelfServiceClients $selfService = null;
    private ?string $selectedTenantId = null;

    /**
     * Create a new Frontegg client
     *
     * @param Config $config The Frontegg configuration
     * @param CacheInterface|null $cache Optional PSR-16 cache implementation for distributed caching
     * @param string $cachePrefix Optional prefix for cache keys
     */
    public function __construct(Config $config, ?CacheInterface $cache = null, string $cachePrefix = 'frontegg_')
    {
        $this->config = $config;
        $this->httpClient = new FronteggHttpClient($config);
        $this->identityManager = new IdentityManager($config, $this->httpClient, $cache);

        // Set custom cache prefix if provided and cache is available
        if ($cache !== null && $cachePrefix !== 'frontegg_') {
            $this->identityManager->setCache($cache, $cachePrefix);
        }

        $this->httpClient->setIdentityManager($this->identityManager);
    }

    /**
     * Authenticate a request using the Bearer token from the Authorization header
     *
     * @param string $token
     *
     * @return bool True if authentication was successful
     * @throws TokenValidationException If the token is invalid or expired
     * @throws UnauthorizedException If the token is invalid or expired
     */
    public function authenticate(string $token): bool
    {
        try {
            $this->identityManager->setToken($token);
            if ($this->hasValidUser()) {
                $this->selectTenant($this->getUserClaims()->getTenantId());
            }
            return true;
        } catch (InvalidTokenException $e) {
            throw new UnauthorizedException('Invalid token provided', 0, $e);
        }
    }

    /**
     * Check if the current request has a valid user token
     *
     * @return bool True if a valid user token is present
     */
    public function hasValidUser(): bool
    {
        try {
            $this->identityManager->getUserToken();
            return true;
        } catch (NoTokenException $e) {
            return false;
        }
    }

    /**
     * Check if the current request has a valid tenant token
     *
     * @return bool True if a valid tenant token is present
     */
    public function hasValidTenant(): bool
    {
        try {
            $this->identityManager->getTenantToken();
            return true;
        } catch (NoTokenException $e) {
            return false;
        }
    }

    /**
     * Get the current authenticated user's claims
     *
     * @return UserTokenClaims The user's claims
     * @throws UnauthorizedException If no valid user token is present
     */
    public function getUserClaims(): UserTokenClaims
    {
        try {
            return $this->identityManager->getUserTokenClaims();
        } catch (NoTokenException $e) {
            throw new UnauthorizedException('No authenticated user present', 0, $e);
        }
    }

    /**
     * Get the current tenant's claims
     *
     * @return TenantTokenClaims The tenant's claims
     * @throws UnauthorizedException If no valid tenant token is present
     */
    public function getTenantClaims(): TenantTokenClaims
    {
        try {
            return $this->identityManager->getTenantTokenClaims();
        } catch (NoTokenException $e) {
            throw new UnauthorizedException('No authenticated tenant present', 0, $e);
        }
    }

    /**
     * Check if the current user has the specified permission
     *
     * @param string $permission The permission to check for
     * @return bool True if the user has the permission
     */
    public function hasPermission(string $permission): bool
    {
        try {
            $claims = $this->getUserClaims();
            return in_array($permission, $claims->getPermissions(), true);
        } catch (UnauthorizedException $e) {
            return false;
        }
    }

    /**
     * Check if the current user has any of the specified roles
     *
     * @param array<string> $roles The roles to check for
     * @return bool True if the user has any of the roles
     */
    public function hasAnyRole(array $roles): bool
    {
        try {
            $claims = $this->getUserClaims();
            return !empty(array_intersect($roles, $claims->getRoles()));
        } catch (UnauthorizedException $e) {
            return false;
        }
    }

    /**
     * Get the current token for API requests
     *
     * @return string The current token
     * @throws UnauthorizedException If no valid token is present
     */
    public function getCurrentToken(): string
    {
        try {
            return $this->identityManager->getToken();
        } catch (NoTokenException $e) {
            throw new UnauthorizedException('No valid token present', 0, $e);
        }
    }

    public function management(): ManagementClients
    {
        if ($this->management === null) {
            $this->management = new ManagementClients(
                $this->config,
                $this->identityManager,
                $this->httpClient,
                $this->selectedTenantId
            );
        }
        return $this->management;
    }

    public function selfService(): SelfServiceClients
    {
        if ($this->selfService === null) {
            $this->selfService = new SelfServiceClients(
                $this->config,
                $this->identityManager,
                $this->httpClient,
                $this->selectedTenantId
            );
        }
        return $this->selfService;
    }

    /**
     * Set a cache implementation for the identity manager
     *
     * @param CacheInterface $cache PSR-16 compatible cache implementation
     * @param string $prefix Optional prefix for cache keys
     * @return void
     */
    public function setCache(CacheInterface $cache, string $prefix = 'frontegg_'): void
    {
        $this->identityManager->setCache($cache, $prefix);
    }

    /**
     * Get the identity manager for token and authentication operations
     */
    public function identity(): IdentityManager
    {
        return $this->identityManager;
    }

    /**
     * Select a tenant for the current session
     *
     * @param string $tenantId The ID of the tenant to select
     * @return void
     */
    public function selectTenant(string $tenantId): void
    {
        $this->selectedTenantId = $tenantId;
        // Clear cached client instances so they get recreated with the new tenant ID
        $this->management = null;
        $this->selfService = null;
    }

    /**
     * Get the ID of the currently selected tenant
     *
     * @return string|null The ID of the selected tenant, or null if no tenant is selected
     */
    public function getSelectedTenantId(): ?string
    {
        return $this->selectedTenantId;
    }

    /**
     * Check if a tenant is currently selected
     *
     * @return bool True if a tenant is selected, false otherwise
     */
    public function hasSelectedTenant(): bool
    {
        return $this->selectedTenantId !== null;
    }

    /**
     * Clear the currently selected tenant
     *
     * @return void
     */
    public function clearSelectedTenant(): void
    {
        $this->selectedTenantId = null;
        // Clear cached client instances so they get recreated without the tenant ID
        $this->management = null;
        $this->selfService = null;
    }
}
