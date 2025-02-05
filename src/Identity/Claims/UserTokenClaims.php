<?php

namespace Frontegg\Identity\Claims;

use Lcobucci\JWT\UnencryptedToken;

/**
 * Represents the claims contained within a Frontegg user JWT token
 *
 * This class extends the base TokenClaims to handle user-specific claims,
 * including personal information, permissions, roles, and tenant associations.
 * It provides methods to access user information and check authorization.
 */
class UserTokenClaims extends TokenClaims
{
    // Standard JWT claim names
    private const CLAIM_SUBJECT = 'subject';

    // Frontegg-specific user claim names
    private const CLAIM_NAME = 'name';
    private const CLAIM_EMAIL = 'email';
    private const CLAIM_EMAIL_VERIFIED = 'emailVerified';
    private const CLAIM_TENANT_IDS = 'tenantIds';
    private const CLAIM_PROFILE_PICTURE = 'profilePictureUrl';
    private const CLAIM_PERMISSIONS = 'permissions';
    private const CLAIM_ROLES = 'roles';
    private const CLAIM_TENANT_ID = 'tenantId';

    /**
     * Creates a new UserTokenClaims instance from a JWT token
     *
     * @param UnencryptedToken $token The decoded JWT token containing user claims
     */
    public function __construct(UnencryptedToken $token)
    {
        parent::__construct($token);
    }

    /**
     * Gets the user's full name
     *
     * @return string The user's name, may be empty if not provided
     */
    public function getName(): string
    {
        return $this->token->claims()->get(self::CLAIM_NAME, '');
    }

    /**
     * Gets the user's first name
     *
     * This method attempts to extract the first name from the full name.
     * For names with multiple parts, it returns the first part.
     * For single word names, it returns the entire name.
     *
     * @return string The user's first name, may be empty if no name is provided
     */
    public function getFirstName(): string
    {
        $name = trim($this->getName());
        if (empty($name)) {
            return '';
        }

        $parts = preg_split('/\s+/', $name, 2);
        return $parts[0];
    }

    /**
     * Gets the user's last name
     *
     * This method attempts to extract the last name from the full name.
     * For names with multiple parts, it returns everything after the first part.
     * For single word names, it returns an empty string.
     *
     * @return string The user's last name, may be empty if no last name is found
     */
    public function getLastName(): string
    {
        $name = trim($this->getName());
        if (empty($name)) {
            return '';
        }

        $parts = preg_split('/\s+/', $name, 2);
        return count($parts) > 1 ? $parts[1] : '';
    }

    /**
     * Gets the user's email address
     *
     * @return string The user's email address, may be empty if not provided
     */
    public function getEmail(): string
    {
        return $this->token->claims()->get(self::CLAIM_EMAIL, '');
    }

    /**
     * Checks if the user's email has been verified
     *
     * @return bool True if the email is verified, false otherwise
     */
    public function isEmailVerified(): bool
    {
        return $this->token->claims()->get(self::CLAIM_EMAIL_VERIFIED, false);
    }

    /**
     * Gets all tenant IDs the user has access to
     *
     * @return array<string> Array of tenant IDs
     */
    public function getTenantIds(): array
    {
        return array_map('strval', $this->token->claims()->get(self::CLAIM_TENANT_IDS, []));
    }

    /**
     * Gets the URL of the user's profile picture
     *
     * @return string The profile picture URL, may be empty if not provided
     */
    public function getProfilePictureUrl(): string
    {
        return $this->token->claims()->get(self::CLAIM_PROFILE_PICTURE, '');
    }

    /**
     * Gets the list of permissions granted to the user
     *
     * @return array<string> List of permission strings
     */
    public function getPermissions(): array
    {
        return $this->token->claims()->get(self::CLAIM_PERMISSIONS, []);
    }

    /**
     * Gets the list of roles assigned to the user
     *
     * @return array<string> List of role names
     */
    public function getRoles(): array
    {
        return $this->token->claims()->get(self::CLAIM_ROLES, []);
    }

    /**
     * Checks if the user has a specific role
     *
     * Role comparison is case-insensitive for flexibility.
     *
     * @param string $role The role to check for
     * @return bool True if the user has the role, false otherwise
     */
    public function hasRole(string $role): bool
    {
        return in_array(strtolower($role), array_map('strtolower', $this->getRoles()), true);
    }

    /**
     * Gets the ID of the user's current tenant
     *
     * @return string The current tenant ID, may be empty if not set
     */
    public function getTenantId(): string
    {
        return $this->token->claims()->get(self::CLAIM_TENANT_ID, '');
    }

    /**
     * Checks if the user has a specific permission
     *
     * This method supports wildcard permissions. For example, if a user has the permission
     * 'admin.*', they will be granted any permission that starts with 'admin.'.
     *
     * @param string $permission The permission to check for
     * @return bool True if the user has the permission or a matching wildcard permission
     */
    public function hasPermission(string $permission): bool
    {
        // Check for exact permission match
        if (in_array($permission, $this->getPermissions(), true)) {
            return true;
        }

        // Check for wildcard permissions
        foreach ($this->getPermissions() as $userPermission) {
            if (str_ends_with($userPermission, '.*')) {
                $prefix = rtrim($userPermission, '.*');
                if (str_starts_with($permission, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Gets the unique identifier for the user
     *
     * @return string|null The user's ID from the subject claim, null if not present
     */
    public function getUserId(): ?string
    {
        $userId = $this->token->claims()->get(self::CLAIM_SUBJECT, null);
        return $userId !== null ? (string)$userId : null;
    }

    /**
     * Converts the claims to a JSON-serializable array
     *
     * @return array<string, mixed> Array representation of all claims including user-specific ones
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            self::CLAIM_NAME => $this->getName(),
            self::CLAIM_EMAIL => $this->getEmail(),
            self::CLAIM_EMAIL_VERIFIED => $this->isEmailVerified(),
            self::CLAIM_TENANT_IDS => $this->getTenantIds(),
            self::CLAIM_PROFILE_PICTURE => $this->getProfilePictureUrl(),
            self::CLAIM_PERMISSIONS => $this->getPermissions(),
            self::CLAIM_ROLES => $this->getRoles(),
            self::CLAIM_TENANT_ID => $this->getTenantId(),
            self::CLAIM_SUBJECT => $this->getUserId(),
        ]);
    }
}
