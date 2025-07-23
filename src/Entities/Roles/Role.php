<?php

namespace Frontegg\Entities\Roles;

use DateTimeImmutable;
use JsonSerializable;

class Role implements JsonSerializable
{
    private string $id;
    private string $vendorId;
    private string $tenantId;
    private string $key;
    private string $name;
    private ?string $description;
    private bool $isDefault;
    private bool $firstUserRole;
    private int $level;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;
    private array $permissions;

    public function __construct(array $roleData)
    {
        $this->id = $roleData['id'];
        $this->vendorId = $roleData['vendorId'];
        $this->tenantId = $roleData['tenantId'];
        $this->key = $roleData['key'];
        $this->name = $roleData['name'];
        $this->description = $roleData['description'] ?? null;
        $this->isDefault = $roleData['isDefault'] ?? false;
        $this->firstUserRole = $roleData['firstUserRole'] ?? false;
        $this->level = $roleData['level'] ?? 0;
        $this->createdAt = new DateTimeImmutable($roleData['createdAt']);
        $this->updatedAt = new DateTimeImmutable($roleData['updatedAt']);
        
        // Handle permissions array
        $this->permissions = array_map(
            fn(array $permission) => [
                'id' => $permission['id'],
                'key' => $permission['key'],
                'name' => $permission['name'] ?? null,
                'description' => $permission['description'] ?? null,
                'categoryId' => $permission['categoryId'] ?? null,
                'fePermission' => $permission['fePermission'] ?? false,
                'createdAt' => isset($permission['createdAt']) ? new DateTimeImmutable($permission['createdAt']) : null,
                'updatedAt' => isset($permission['updatedAt']) ? new DateTimeImmutable($permission['updatedAt']) : null,
            ],
            $roleData['permissions'] ?? []
        );
    }

    /**
     * Get the role ID
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the vendor ID
     */
    public function getVendorId(): string
    {
        return $this->vendorId;
    }

    /**
     * Get the tenant ID
     */
    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    /**
     * Get the role key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the role name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the role description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Check if this is a default role
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * Check if this is a first user role
     */
    public function isFirstUserRole(): bool
    {
        return $this->firstUserRole;
    }

    /**
     * Get the role level
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Get the creation date
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Get the last update date
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Get all permissions associated with this role
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Get permission IDs associated with this role
     */
    public function getPermissionIds(): array
    {
        return array_column($this->permissions, 'id');
    }

    /**
     * Get permission keys associated with this role
     */
    public function getPermissionKeys(): array
    {
        return array_column($this->permissions, 'key');
    }

    /**
     * Check if the role has a specific permission by ID
     */
    public function hasPermission(string $permissionId): bool
    {
        return in_array($permissionId, $this->getPermissionIds(), true);
    }

    /**
     * Check if the role has a specific permission by key
     */
    public function hasPermissionKey(string $permissionKey): bool
    {
        return in_array($permissionKey, $this->getPermissionKeys(), true);
    }

    /**
     * Check if the role has any of the specified permissions by ID
     */
    public function hasAnyPermission(array $permissionIds): bool
    {
        return !empty(array_intersect($permissionIds, $this->getPermissionIds()));
    }

    /**
     * Check if the role has any of the specified permissions by key
     */
    public function hasAnyPermissionKey(array $permissionKeys): bool
    {
        return !empty(array_intersect($permissionKeys, $this->getPermissionKeys()));
    }

    /**
     * Check if the role has all of the specified permissions by ID
     */
    public function hasAllPermissions(array $permissionIds): bool
    {
        return empty(array_diff($permissionIds, $this->getPermissionIds()));
    }

    /**
     * Check if the role has all of the specified permissions by key
     */
    public function hasAllPermissionKeys(array $permissionKeys): bool
    {
        return empty(array_diff($permissionKeys, $this->getPermissionKeys()));
    }

    /**
     * Get a specific permission by ID
     */
    public function getPermission(string $permissionId): ?array
    {
        foreach ($this->permissions as $permission) {
            if ($permission['id'] === $permissionId) {
                return $permission;
            }
        }
        return null;
    }

    /**
     * Get a specific permission by key
     */
    public function getPermissionByKey(string $permissionKey): ?array
    {
        foreach ($this->permissions as $permission) {
            if ($permission['key'] === $permissionKey) {
                return $permission;
            }
        }
        return null;
    }

    /**
     * Convert the role to an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'vendorId' => $this->vendorId,
            'tenantId' => $this->tenantId,
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'isDefault' => $this->isDefault,
            'firstUserRole' => $this->firstUserRole,
            'level' => $this->level,
            'createdAt' => $this->createdAt->format('c'),
            'updatedAt' => $this->updatedAt->format('c'),
            'permissions' => array_map(function (array $permission) {
                return [
                    'id' => $permission['id'],
                    'key' => $permission['key'],
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'categoryId' => $permission['categoryId'],
                    'fePermission' => $permission['fePermission'],
                    'createdAt' => $permission['createdAt']?->format('c'),
                    'updatedAt' => $permission['updatedAt']?->format('c'),
                ];
            }, $this->permissions),
        ];
    }

    /**
     * JSON serialization
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * String representation of the role
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
