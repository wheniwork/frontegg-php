<?php

namespace Frontegg\Entities\Roles\Management;

use Frontegg\Clients\Management\BaseManagementClient;
use Frontegg\Entities\Roles\Exception\RolesNotFoundException;
use Frontegg\Entities\Roles\Exception\RoleValidationException;
use Frontegg\Entities\Roles\Exception\RoleAlreadyExistsException;
use Frontegg\Exception\HttpException;

class RolesClient extends BaseManagementClient
{
    /**
     * Get all roles for a tenant
     *
     * @param string $tenantId The tenant ID to get roles for
     * @return array Array of role objects
     * @throws RolesNotFoundException
     * @throws HttpException
     */
    public function getRoles(string $tenantId): array
    {
        try {
            $response = $this->httpClient->get('/identity/resources/roles/v1', [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $tenantId,
                ]),
            ]);

            return $response;
        } catch (HttpException $e) {
            throw new RolesNotFoundException('Failed to retrieve roles: ' . $e->getMessage());
        }
    }

    /**
     * Create new roles
     *
     * @param string $tenantId The tenant ID to create roles for
     * @param array $rolesData Array of role data objects
     * @return array Array of created role objects
     * @throws RoleAlreadyExistsException
     * @throws RoleValidationException
     * @throws HttpException
     */
    public function createRoles(string $tenantId, array $rolesData): array
    {
        try {
            $response = $this->httpClient->post('/identity/resources/roles/v1', [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $tenantId,
                ]),
                'json' => $rolesData,
            ]);

            return $response;
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
     * @param string $tenantId The tenant ID
     * @param string $roleId The role ID to update
     * @param array $roleData Updated role data
     * @return array Updated role object
     * @throws RolesNotFoundException
     * @throws RoleValidationException
     * @throws HttpException
     */
    public function updateRole(string $tenantId, string $roleId, array $roleData): array
    {
        try {
            $response = $this->httpClient->patch("/identity/resources/roles/v1/{$roleId}", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $tenantId,
                ]),
                'json' => $roleData,
            ]);

            return $response;
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
     * @param string $tenantId The tenant ID
     * @param string $roleId The role ID to delete
     * @return bool True on successful deletion
     * @throws RolesNotFoundException
     * @throws HttpException
     */
    public function deleteRole(string $tenantId, string $roleId): bool
    {
        try {
            $this->httpClient->delete("/identity/resources/roles/v1/{$roleId}", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $tenantId,
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
     * @param string $tenantId The tenant ID
     * @param string $roleId The role ID
     * @param array $permissionIds Array of permission IDs to assign to the role
     * @return array Updated role object with permissions
     * @throws RolesNotFoundException
     * @throws HttpException
     */
    public function setRolePermissions(string $tenantId, string $roleId, array $permissionIds): array
    {
        try {
            $response = $this->httpClient->put("/identity/resources/roles/v1/{$roleId}/permissions", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $tenantId,
                ]),
                'json' => ['permissionIds' => $permissionIds],
            ]);

            return $response;
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
     * @param string $tenantId The tenant ID
     * @param string $roleId The role ID
     * @param array $permissionIds Array of permission IDs to add to the role
     * @return array Updated role object with permissions
     * @throws RolesNotFoundException
     * @throws HttpException
     */
    public function addRolePermissions(string $tenantId, string $roleId, array $permissionIds): array
    {
        try {
            $response = $this->httpClient->post("/identity/resources/roles/v1/{$roleId}/permissions", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $tenantId,
                ]),
                'json' => ['permissionIds' => $permissionIds],
            ]);

            return $response;
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
     * @param string $tenantId The tenant ID
     * @param string $roleId The role ID
     * @param array $permissionIds Array of permission IDs to remove from the role
     * @return array Updated role object with permissions
     * @throws RolesNotFoundException
     * @throws HttpException
     */
    public function removeRolePermissions(string $tenantId, string $roleId, array $permissionIds): array
    {
        try {
            $response = $this->httpClient->delete("/identity/resources/roles/v1/{$roleId}/permissions", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $tenantId,
                ]),
                'json' => ['permissionIds' => $permissionIds],
            ]);

            return $response;
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
     * @param string $tenantId The tenant ID
     * @param string $roleId The role ID
     * @return array Array of permission objects for the role
     * @throws RolesNotFoundException
     * @throws HttpException
     */
    public function getRolePermissions(string $tenantId, string $roleId): array
    {
        try {
            $response = $this->httpClient->get("/identity/resources/roles/v1/{$roleId}/permissions", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $tenantId,
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
