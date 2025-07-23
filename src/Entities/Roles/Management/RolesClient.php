<?php

namespace Frontegg\Entities\Roles\Management;

use Frontegg\Clients\Management\BaseManagementClient;
use Frontegg\Entities\Roles\Exception\RolesNotFoundException;
use Frontegg\Entities\Roles\Exception\RoleValidationException;
use Frontegg\Entities\Roles\Exception\RoleAlreadyExistsException;
use Frontegg\Entities\Roles\Role;
use Frontegg\Exception\HttpException;

class RolesClient extends BaseManagementClient
{
    /**
     * Get all roles for a tenant
     *
     * @param string|null $tenantId The tenant ID to get roles for (optional if tenant is selected globally)
     * @return Role[] Array of Role objects
     * @throws RolesNotFoundException
     * @throws HttpException
     */
    public function getRoles(?string $tenantId = null): array
    {
        $resolvedTenantId = $this->resolveTenantId($tenantId);
        
        try {
            $response = $this->httpClient->get('/identity/resources/roles/v1', [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $resolvedTenantId,
                ]),
            ]);

            return array_map(fn(array $roleData) => new Role($roleData), $response);
        } catch (HttpException $e) {
            throw new RolesNotFoundException('Failed to retrieve roles: ' . $e->getMessage());
        }
    }

    /**
     * Create new roles
     *
     * @param array $rolesData Array of role data objects
     * @param string|null $tenantId The tenant ID to create roles for (optional if tenant is selected globally)
     * @return Role[] Array of created Role objects
     * @throws RoleAlreadyExistsException
     * @throws RoleValidationException
     * @throws HttpException
     */
    public function createRoles(array $rolesData, ?string $tenantId = null): array
    {
        $resolvedTenantId = $this->resolveTenantId($tenantId);
        
        try {
            $response = $this->httpClient->post('/identity/resources/roles/v1', [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $resolvedTenantId,
                ]),
                'json' => $rolesData,
            ]);

            return array_map(fn(array $roleData) => new Role($roleData), $response);
        } catch (HttpException $e) {
            if ($e->getCode() === 409) {
                throw new RoleAlreadyExistsException('Role with this key already exists');
            }
            if ($e->getCode() === 400) {
                throw new RoleValidationException('Invalid role data: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Update an existing role
     *
     * @param string $roleId The role ID to update
     * @param array $roleData Updated role data
     * @param string|null $tenantId The tenant ID (optional if tenant is selected globally)
     * @return Role Updated Role object
     * @throws RolesNotFoundException
     * @throws RoleValidationException
     * @throws HttpException
     */
    public function updateRole(string $roleId, array $roleData, ?string $tenantId = null): Role
    {
        $resolvedTenantId = $this->resolveTenantId($tenantId);
        
        try {
            $response = $this->httpClient->patch("/identity/resources/roles/v1/{$roleId}", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $resolvedTenantId,
                ]),
                'json' => $roleData,
            ]);

            return new Role($response);
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new RolesNotFoundException("Role with ID {$roleId} not found");
            }
            if ($e->getCode() === 400) {
                throw new RoleValidationException('Invalid role data: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Delete a role
     *
     * @param string $roleId The role ID to delete
     * @param string|null $tenantId The tenant ID (optional if tenant is selected globally)
     * @return bool True on successful deletion
     * @throws RolesNotFoundException
     * @throws HttpException
     */
    public function deleteRole(string $roleId, ?string $tenantId = null): bool
    {
        $resolvedTenantId = $this->resolveTenantId($tenantId);
        
        try {
            $this->httpClient->delete("/identity/resources/roles/v1/{$roleId}", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $resolvedTenantId,
                ]),
            ]);

            return true;
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new RolesNotFoundException("Role with ID {$roleId} not found");
            }
            throw $e;
        }
    }

    /**
     * Set permissions for a role
     *
     * @param string $roleId The role ID
     * @param array $permissionIds Array of permission IDs to assign to the role
     * @param string|null $tenantId The tenant ID (optional if tenant is selected globally)
     * @return Role Updated Role object with permissions
     * @throws RolesNotFoundException
     * @throws HttpException
     */
    public function setRolePermissions(string $roleId, array $permissionIds, ?string $tenantId = null): Role
    {
        $resolvedTenantId = $this->resolveTenantId($tenantId);
        
        try {
            $response = $this->httpClient->put("/identity/resources/roles/v1/{$roleId}/permissions", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $resolvedTenantId,
                ]),
                'json' => ['permissionIds' => $permissionIds],
            ]);

            return new Role($response);
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new RolesNotFoundException("Role with ID {$roleId} not found");
            }
            throw $e;
        }
    }

    /**
     * Add permissions to a role
     *
     * @param string $roleId The role ID
     * @param array $permissionIds Array of permission IDs to add to the role
     * @param string|null $tenantId The tenant ID (optional if tenant is selected globally)
     * @return Role Updated Role object with permissions
     * @throws RolesNotFoundException
     * @throws HttpException
     */
    public function addRolePermissions(string $roleId, array $permissionIds, ?string $tenantId = null): Role
    {
        $resolvedTenantId = $this->resolveTenantId($tenantId);
        
        try {
            $response = $this->httpClient->post("/identity/resources/roles/v1/{$roleId}/permissions", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $resolvedTenantId,
                ]),
                'json' => ['permissionIds' => $permissionIds],
            ]);

            return new Role($response);
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new RolesNotFoundException("Role with ID {$roleId} not found");
            }
            throw $e;
        }
    }

    /**
     * Remove permissions from a role
     *
     * @param string $roleId The role ID
     * @param array $permissionIds Array of permission IDs to remove from the role
     * @param string|null $tenantId The tenant ID (optional if tenant is selected globally)
     * @return Role Updated Role object with permissions
     * @throws RolesNotFoundException
     * @throws HttpException
     */
    public function removeRolePermissions(string $roleId, array $permissionIds, ?string $tenantId = null): Role
    {
        $resolvedTenantId = $this->resolveTenantId($tenantId);
        
        try {
            $response = $this->httpClient->delete("/identity/resources/roles/v1/{$roleId}/permissions", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $resolvedTenantId,
                ]),
                'json' => ['permissionIds' => $permissionIds],
            ]);

            return new Role($response);
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new RolesNotFoundException("Role with ID {$roleId} not found");
            }
            throw $e;
        }
    }

    /**
     * Get permissions for a specific role
     *
     * @param string $roleId The role ID
     * @param string|null $tenantId The tenant ID (optional if tenant is selected globally)
     * @return array Array of permission objects for the role
     * @throws RolesNotFoundException
     * @throws HttpException
     */
    public function getRolePermissions(string $roleId, ?string $tenantId = null): array
    {
        $resolvedTenantId = $this->resolveTenantId($tenantId);
        
        try {
            $response = $this->httpClient->get("/identity/resources/roles/v1/{$roleId}/permissions", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $resolvedTenantId,
                ]),
            ]);

            return $response;
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new RolesNotFoundException("Role with ID {$roleId} not found");
            }
            throw $e;
        }
    }
}
