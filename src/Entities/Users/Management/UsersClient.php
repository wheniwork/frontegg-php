<?php

namespace Frontegg\Entities\Users\Management;

use Frontegg\Clients\Management\BaseManagementClient;
use Frontegg\Entities\Users\Exception\UserAlreadyExistsException;
use Frontegg\Entities\Users\Exception\UserNotFoundException;
use Frontegg\Entities\Users\Exception\UserValidationException;
use Frontegg\Entities\Users\User;
use Frontegg\Exception\HttpException;

class UsersClient extends BaseManagementClient
{
    /**
     * Get a list of users
     *
     * @param array $options Query parameters and filters
     * @return User[]
     * @throws UserValidationException
     * @throws HttpException
     */
    public function getUsers(array $options = []): array
    {
        try {
            $response = $this->httpClient->get('/identity/resources/users/v2', [
                'headers' => $this->getHeaders(),
                'query' => $options
            ]);
            return array_map(fn(array $userData) => new User($userData), $response['users'] ?? []);
        } catch (HttpException $e) {
            if ($e->getCode() === 400) {
                throw new UserValidationException('Invalid query parameters: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Get a single user by ID
     *
     * @param string $userId
     * @return User
     * @throws UserNotFoundException
     * @throws HttpException
     */
    public function getUser(string $userId): User
    {
        try {
            $userData = $this->httpClient->get("/identity/resources/users/v1/{$userId}", [
                'headers' => $this->getHeaders()
            ]);
            return new User($userData);
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new UserNotFoundException("User not found with ID: {$userId}");
            }
            throw $e;
        }
    }

    /**
     * Delete a user
     *
     * @param string $userId User ID to delete
     * @param string $tenantId Tenant ID the user belongs to
     * @return bool
     * @throws UserNotFoundException
     * @throws HttpException
     */
    public function deleteUser(string $userId, string $tenantId): bool
    {
        try {
            $this->httpClient->delete("/identity/resources/users/v1/{$userId}", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $tenantId
                ])
            ]);
            return true;
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new UserNotFoundException("User not found with ID: {$userId}");
            }
            throw $e;
        }
    }

    /**
     * Add roles to a user
     *
     * @param string $userId User ID to assign roles to
     * @param string $tenantId Tenant ID the user belongs to
     * @param array $roleIds Array of role IDs to assign
     * @return bool
     * @throws UserNotFoundException
     * @throws UserValidationException
     * @throws HttpException
     */
    public function addRolesToUser(string $userId, string $tenantId, array $roleIds): bool
    {
        try {
            $this->httpClient->post("/identity/resources/users/v1/{$userId}/roles", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $tenantId
                ]),
                'json' => $roleIds,
            ]);
            return true;
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new UserNotFoundException("User not found with ID: {$userId}");
            }
            if ($e->getCode() === 400) {
                throw new UserValidationException('Invalid role data: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Remove roles from a user
     *
     * @param string $userId User ID to remove roles from
     * @param string $tenantId Tenant ID the user belongs to
     * @param array $roleIds Array of role IDs to remove
     * @return bool
     * @throws UserNotFoundException
     * @throws UserValidationException
     * @throws HttpException
     */
    public function removeRolesFromUser(string $userId, string $tenantId, array $roleIds): bool
    {
        try {
            $this->httpClient->delete("/identity/resources/users/v1/{$userId}/roles", [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $tenantId
                ]),
                'json' => $roleIds,
            ]);
            return true;
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new UserNotFoundException("User not found with ID: {$userId}");
            }
            if ($e->getCode() === 400) {
                throw new UserValidationException('Invalid role data: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Get a user by ID using the vendor-only endpoint
     * This endpoint retrieves a user regardless of any tenant the user belongs to
     *
     * @param string $userId User ID to retrieve
     * @return User
     * @throws UserNotFoundException
     * @throws HttpException
     */
    public function getVendorUser(string $userId): User
    {
        try {
            $response = $this->httpClient->get("/identity/resources/vendor-only/users/v1/{$userId}", [
                'headers' => $this->getHeaders()
            ]);
            return new User($response);
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                throw new UserNotFoundException("User not found with ID: {$userId}");
            }
            throw $e;
        }
    }
}
