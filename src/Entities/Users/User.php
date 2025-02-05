<?php

namespace Frontegg\Entities\Users;

use DateTimeImmutable;
use JsonSerializable;

class User implements JsonSerializable
{
    private string $id;
    private string $email;
    private ?string $name;
    private ?string $phoneNumber;
    private ?string $profilePictureUrl;
    private array $metadata;
    private array $vendorMetadata;
    private array $tenantIds;
    private array $tenants;
    private bool $mfaEnrolled;
    private bool $activatedForTenant;
    private bool $isDisabled;
    private bool $isLocked;
    private bool $verified;
    private bool $subAccountAccessAllowed;
    private string $managedBy;
    private string $provider;
    private ?DateTimeImmutable $lastLogin;
    private DateTimeImmutable $createdAt;
    private array $roles;
    private array $permissions;
    private array $groups;
    private string $sub;
    private string $tenantId;
    private array $mfaProviders;
    private ?string $source;
    private array $detailedTenants;

    public function __construct(array $userData)
    {
        $this->id = $userData['id'];
        $this->email = $userData['email'];
        $this->name = $userData['name'] ?? null;
        $this->phoneNumber = $userData['phoneNumber'] ?? null;
        $this->profilePictureUrl = $userData['profilePictureUrl'] ?? null;
        $this->metadata = is_string($userData['metadata'] ?? '{}')
            ? json_decode($userData['metadata'], true) ?? []
            : ($userData['metadata'] ?? []);
        $this->vendorMetadata = is_string($userData['vendorMetadata'] ?? '{}')
            ? json_decode($userData['vendorMetadata'], true) ?? []
            : ($userData['vendorMetadata'] ?? []);
        $this->tenantIds = $userData['tenantIds'] ?? [];
        $this->tenants = $userData['tenants'] ?? [];
        $this->mfaEnrolled = $userData['mfaEnrolled'] ?? false;
        $this->activatedForTenant = $userData['activatedForTenant'] ?? false;
        $this->isDisabled = $userData['isDisabled'] ?? false;
        $this->isLocked = $userData['isLocked'] ?? false;
        $this->verified = $userData['verified'] ?? false;
        $this->subAccountAccessAllowed = $userData['subAccountAccessAllowed'] ?? false;
        $this->managedBy = $userData['managedBy'] ?? 'frontegg';
        $this->provider = $userData['provider'] ?? 'frontegg';
        $this->lastLogin = isset($userData['lastLogin'])
            ? new DateTimeImmutable($userData['lastLogin'])
            : null;
        $this->createdAt = new DateTimeImmutable($userData['createdAt']);
        $this->roles = array_map(
            fn(array $role) => [
                'id' => $role['id'],
                'vendorId' => $role['vendorId'] ?? null,
                'name' => $role['name'] ?? null,
                'key' => $role['key'] ?? null,
                'description' => $role['description'] ?? null,
            ],
            $userData['roles'] ?? []
        );
        $this->permissions = array_map(
            fn(array $permission) => [
                'id' => $permission['id'],
                'key' => $permission['key'],
                'name' => $permission['name'] ?? null,
                'description' => $permission['description'] ?? null,
                'categoryId' => $permission['categoryId'] ?? null,
            ],
            $userData['permissions'] ?? []
        );
        $this->groups = $userData['groups'] ?? [];
        $this->sub = $userData['sub'];
        $this->tenantId = $userData['tenantId'];
        $this->mfaProviders = $userData['mfaProviders'] ?? [];
        $this->source = $userData['source'] ?? null;

        // Handle detailed tenant information from vendor-only endpoint
        $this->detailedTenants = array_map(
            fn(array $tenant) => [
                'tenantId' => $tenant['tenantId'],
                'roles' => array_map(
                    fn(array $role) => [
                        'id' => $role['id'],
                        'vendorId' => $role['vendorId'] ?? null,
                        'tenantId' => $role['tenantId'] ?? null,
                        'key' => $role['key'] ?? null,
                        'name' => $role['name'] ?? null,
                        'description' => $role['description'] ?? null,
                        'isDefault' => $role['isDefault'] ?? false,
                        'firstUserRole' => $role['firstUserRole'] ?? false,
                        'createdAt' => isset($role['createdAt']) ? new DateTimeImmutable($role['createdAt']) : null,
                        'updatedAt' => isset($role['updatedAt']) ? new DateTimeImmutable($role['updatedAt']) : null,
                        'permissions' => $role['permissions'] ?? [],
                        'level' => $role['level'] ?? null,
                    ],
                    $tenant['roles'] ?? []
                ),
                'temporaryExpirationDate' => isset($tenant['temporaryExpirationDate'])
                    ? new DateTimeImmutable($tenant['temporaryExpirationDate'])
                    : null,
                'isDisabled' => $tenant['isDisabled'] ?? false,
            ],
            $userData['tenants'] ?? []
        );
    }

    // Getters and utility methods...
    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
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

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function getProfilePictureUrl(): ?string
    {
        return $this->profilePictureUrl;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getVendorMetadata(): array
    {
        return $this->vendorMetadata;
    }

    public function getTenantIds(): array
    {
        return $this->tenantIds;
    }

    public function getTenants(): array
    {
        return $this->tenants;
    }

    public function isMfaEnrolled(): bool
    {
        return $this->mfaEnrolled;
    }

    public function isActivatedForTenant(): bool
    {
        return $this->activatedForTenant;
    }

    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function isSubAccountAccessAllowed(): bool
    {
        return $this->subAccountAccessAllowed;
    }

    public function getManagedBy(): string
    {
        return $this->managedBy;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getLastLogin(): ?DateTimeImmutable
    {
        return $this->lastLogin;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getSub(): string
    {
        return $this->sub;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function isInTenant(string $tenantId): bool
    {
        return in_array($tenantId, $this->tenantIds, true);
    }

    public function getMfaProviders(): array
    {
        return $this->mfaProviders;
    }

    public function hasMfaProvider(string $provider): bool
    {
        return in_array($provider, $this->mfaProviders, true);
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function getRoleByKey(string $key): ?array
    {
        foreach ($this->roles as $role) {
            if (($role['key'] ?? '') === $key) {
                return $role;
            }
        }
        return null;
    }

    public function getPermissionsByCategory(string $categoryId): array
    {
        return array_filter(
            $this->permissions,
            fn($permission) => ($permission['categoryId'] ?? '') === $categoryId
        );
    }

    public function hasPermissionInCategory(string $permissionKey, string $categoryId): bool
    {
        foreach ($this->permissions as $permission) {
            if (($permission['key'] ?? '') === $permissionKey &&
                ($permission['categoryId'] ?? '') === $categoryId) {
                return true;
            }
        }
        return false;
    }

    public function getTenantById(string $tenantId): ?array
    {
        foreach ($this->tenants as $tenant) {
            if (($tenant['tenantId'] ?? '') === $tenantId) {
                return $tenant;
            }
        }
        return null;
    }

    /**
     * Get detailed tenant information including roles and permissions per tenant
     * This is only available when using the vendor-only endpoint
     *
     * @return array
     */
    public function getDetailedTenants(): array
    {
        return $this->detailedTenants;
    }

    /**
     * Get roles for a specific tenant
     * This is only available when using the vendor-only endpoint
     *
     * @param string $tenantId
     * @return array|null Returns null if tenant not found
     */
    public function getRolesForTenant(string $tenantId): ?array
    {
        foreach ($this->detailedTenants as $tenant) {
            if ($tenant['tenantId'] === $tenantId) {
                return $tenant['roles'];
            }
        }
        return null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'phoneNumber' => $this->phoneNumber,
            'profilePictureUrl' => $this->profilePictureUrl,
            'metadata' => json_encode($this->metadata),
            'vendorMetadata' => json_encode($this->vendorMetadata),
            'tenantIds' => $this->tenantIds,
            'tenants' => $this->tenants,
            'mfaEnrolled' => $this->mfaEnrolled,
            'activatedForTenant' => $this->activatedForTenant,
            'isDisabled' => $this->isDisabled,
            'isLocked' => $this->isLocked,
            'verified' => $this->verified,
            'subAccountAccessAllowed' => $this->subAccountAccessAllowed,
            'managedBy' => $this->managedBy,
            'provider' => $this->provider,
            'lastLogin' => $this->lastLogin?->format('Y-m-d\TH:i:s.v\Z'),
            'createdAt' => $this->createdAt->format('Y-m-d\TH:i:s.v\Z'),
            'roles' => $this->roles,
            'permissions' => $this->permissions,
            'groups' => $this->groups,
            'sub' => $this->sub,
            'tenantId' => $this->tenantId,
            'mfaProviders' => $this->mfaProviders,
            'source' => $this->source,
            'detailedTenants' => $this->detailedTenants,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
