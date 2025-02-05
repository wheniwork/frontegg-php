<?php

namespace Frontegg\Entities\Users\SelfService;

use Frontegg\Clients\SelfService\BaseSelfServiceClient;
use Frontegg\Exception\HttpException;
use Frontegg\Entities\Users\User;
use Frontegg\Entities\Users\Exception\UserAlreadyExistsException;
use Frontegg\Entities\Users\Exception\UserNotFoundException;
use Frontegg\Entities\Users\Exception\UserValidationException;

class UsersClient extends BaseSelfServiceClient
{
    /**
     * Create a User instance
     */
    private function createUser(array $userData): User
    {
        return new User($userData);
    }

    /**
     * Update the current user's information
     *
     * @param array $data Data to update (name, phoneNumber, profilePictureUrl, metadata)
     * @return User
     * @throws HttpException
     */
    public function updateProfile(array $data): User
    {
        $allowedFields = ['name', 'phoneNumber', 'profilePictureUrl', 'metadata'];
        $invalidFields = array_diff(array_keys($data), $allowedFields);
        if (!empty($invalidFields)) {
            throw new \InvalidArgumentException('Invalid fields provided: ' . implode(', ', $invalidFields));
        }

        $response = $this->httpClient->put('/identity/resources/users/v1/me', [
            'headers' => $this->getHeaders(),
            'json' => array_filter($data),
        ]);

        return $this->createUser($response);
    }

    /**
     * Get the current authenticated user's profile
     *
     * @return User
     * @throws HttpException
     */
    public function getProfile(): User
    {
        $userData = $this->httpClient->get('/identity/resources/users/v3/me', [
            'headers' => $this->getHeaders()
        ]);
        return $this->createUser($userData);
    }

    /**
     * Update MFA settings for the current user
     *
     * @param string $provider MFA provider (e.g., 'sms', 'authenticator')
     * @param bool $enabled Whether to enable or disable the provider
     * @return User
     * @throws HttpException
     */
    public function updateMfaSettings(string $provider, bool $enabled): User
    {
        $endpoint = $enabled ? 'enable' : 'disable';
        $response = $this->httpClient->post("/identity/resources/users/v1/mfa/{$provider}/{$endpoint}", [
            'headers' => $this->getHeaders()
        ]);
        return $this->createUser($response);
    }

    /**
     * Invite a new user to a tenant
     *
     * @param string $tenantId The tenant to invite the user to
     * @param array $userData User invitation data
     * @return User
     * @throws UserAlreadyExistsException
     * @throws UserValidationException
     * @throws HttpException
     */
    public function inviteUser(string $tenantId, array $userData): User
    {
        try {
            $response = $this->httpClient->post('/identity/resources/users/v2', [
                'headers' => $this->getHeaders([
                    'frontegg-tenant-id' => $tenantId
                ]),
                'json' => $userData,
            ]);
            return new User($response);
        } catch (HttpException $e) {
            if ($e->getCode() === 409) {
                throw new UserAlreadyExistsException('User with this email already exists');
            }
            if ($e->getCode() === 400) {
                throw new UserValidationException('Invalid user data: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Add roles to a user
     *
     * @param string $userId User ID to assign roles to
     * @param array $roleIds Array of role IDs to assign
     * @return bool
     * @throws UserNotFoundException
     * @throws UserValidationException
     * @throws HttpException
     */
    public function addRolesToUser(string $userId, array $roleIds): bool
    {
        try {
            $this->httpClient->post("/identity/resources/users/v1/{$userId}/roles", [
                'headers' => $this->getHeaders(),
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
     * @param array $roleIds Array of role IDs to remove
     * @return bool
     * @throws UserNotFoundException
     * @throws UserValidationException
     * @throws HttpException
     */
    public function removeRolesFromUser(string $userId, array $roleIds): bool
    {
        try {
            $this->httpClient->delete("/identity/resources/users/v1/{$userId}/roles", [
                'headers' => $this->getHeaders(),
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
}
