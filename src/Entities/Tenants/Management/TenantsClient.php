<?php

namespace Frontegg\Entities\Tenants\Management;

use Frontegg\Clients\Management\BaseManagementClient;
use Frontegg\Entities\Tenants\Exception\TenantNotFoundException;
use Frontegg\Entities\Tenants\Exception\TenantValidationException;
use Frontegg\Entities\Tenants\Tenant;
use Frontegg\Exception\HttpException;

class TenantsClient extends BaseManagementClient
{
    /**
     * Get a list of tenants
     *
     * @param array $options Query parameters for filtering and pagination
     * @return Tenant[] Array of Tenant objects
     * @throws TenantValidationException
     * @throws HttpException
     */
    public function getTenants(array $options = []): array
    {
        try {
            $response = $this->httpClient->get('/resources/tenants/v1', [
                'headers' => $this->getHeaders(),
                'query' => $options
            ]);
            return array_map(fn(array $data) => new Tenant($data), $response['tenants'] ?? []);
        } catch (HttpException $e) {
            if ($e->getCode() === 400) {
                throw new TenantValidationException('Invalid query parameters: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Get a tenant by ID
     *
     * @param string $tenantId The tenant ID
     * @return Tenant
     * @throws TenantNotFoundException
     * @throws HttpException
     */
    public function getTenant(string $tenantId): Tenant
    {
        try {
            $data = $this->httpClient->get("/resources/tenants/v1/{$tenantId}", [
                'headers' => $this->getHeaders()
            ]);
            return new Tenant($data);
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new TenantNotFoundException("Tenant not found with ID: {$tenantId}");
            }
            throw $e;
        }
    }

    /**
     * Create a new tenant
     *
     * @param array $data Tenant data
     * @return Tenant
     * @throws TenantValidationException
     * @throws HttpException
     */
    public function createTenant(array $data): Tenant
    {
        try {
            $response = $this->httpClient->post('/resources/tenants/v1', [
                'headers' => $this->getHeaders(),
                'json' => $data,
            ]);
            return new Tenant($response);
        } catch (HttpException $e) {
            if ($e->getCode() === 400) {
                throw new TenantValidationException('Invalid tenant data: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Update a tenant
     *
     * @param string $tenantId The tenant ID
     * @param array $data Updated tenant data
     * @return Tenant
     * @throws TenantNotFoundException
     * @throws TenantValidationException
     * @throws HttpException
     */
    public function updateTenant(string $tenantId, array $data): Tenant
    {
        try {
            $response = $this->httpClient->put("/resources/tenants/v2/{$tenantId}", [
                'headers' => $this->getHeaders(),
                'json' => $data,
            ]);
            return new Tenant($response);
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new TenantNotFoundException("Tenant not found with ID: {$tenantId}");
            }
            if ($e->getCode() === 400) {
                throw new TenantValidationException('Invalid tenant data: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Delete a tenant
     *
     * @param string $tenantId The tenant ID
     * @return bool True if successful
     * @throws TenantNotFoundException
     * @throws HttpException
     */
    public function deleteTenant(string $tenantId): bool
    {
        try {
            $this->httpClient->delete("/resources/tenants/v1/{$tenantId}", [
                'headers' => $this->getHeaders(),
            ]);
            return true;
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new TenantNotFoundException("Tenant not found with ID: {$tenantId}");
            }
            throw $e;
        }
    }

    /**
     * Update tenant metadata
     *
     * @param string $tenantId The tenant ID
     * @param array $data Metadata to update
     * @return Tenant
     * @throws TenantNotFoundException
     * @throws TenantValidationException
     * @throws HttpException
     */
    public function updateMetadata(string $tenantId, array $data): Tenant
    {
        try {
            $response = $this->httpClient->patch("/resources/tenants/v1/{$tenantId}/metadata", [
                'headers' => $this->getHeaders(),
                'json' => $data,
            ]);
            return new Tenant($response);
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new TenantNotFoundException("Tenant not found with ID: {$tenantId}");
            }
            if ($e->getCode() === 400) {
                throw new TenantValidationException('Invalid metadata: ' . $e->getMessage());
            }
            throw $e;
        }
    }
}
