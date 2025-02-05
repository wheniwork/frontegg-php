<?php

namespace Frontegg\Entities\Tenants;

use DateTimeImmutable;
use JsonSerializable;

class Tenant implements JsonSerializable
{
    private string $id;
    private string $name;
    private ?string $websiteUrl;
    private array $metadata;
    private array $vendorMetadata;
    private array $features;
    private array $roles;
    private array $groups;
    private array $users;
    private string $tenantId;
    private bool $isLocked;
    private bool $backOfficeEnabled;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $lastLogin;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->websiteUrl = $data['websiteUrl'] ?? null;
        $this->metadata = is_string($data['metadata'] ?? '{}') 
            ? json_decode($data['metadata'], true) ?? []
            : ($data['metadata'] ?? []);
        $this->vendorMetadata = is_string($data['vendorMetadata'] ?? '{}') 
            ? json_decode($data['vendorMetadata'], true) ?? []
            : ($data['vendorMetadata'] ?? []);
        $this->features = $data['features'] ?? [];
        $this->roles = $data['roles'] ?? [];
        $this->groups = $data['groups'] ?? [];
        $this->users = $data['users'] ?? [];
        $this->tenantId = $data['tenantId'];
        $this->isLocked = $data['isLocked'] ?? false;
        $this->backOfficeEnabled = $data['backOfficeEnabled'] ?? false;
        $this->createdAt = new DateTimeImmutable($data['createdAt']);
        $this->lastLogin = isset($data['lastLogin']) 
            ? new DateTimeImmutable($data['lastLogin'])
            : null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getVendorMetadata(): array
    {
        return $this->vendorMetadata;
    }

    public function getFeatures(): array
    {
        return $this->features;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    public function isBackOfficeEnabled(): bool
    {
        return $this->backOfficeEnabled;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastLogin(): ?DateTimeImmutable
    {
        return $this->lastLogin;
    }

    public function hasFeature(string $featureKey): bool
    {
        foreach ($this->features as $feature) {
            if (($feature['key'] ?? '') === $featureKey) {
                return true;
            }
        }
        return false;
    }

    public function hasRole(string $roleKey): bool
    {
        foreach ($this->roles as $role) {
            if (($role['key'] ?? '') === $roleKey) {
                return true;
            }
        }
        return false;
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

    public function getGroupByKey(string $key): ?array
    {
        foreach ($this->groups as $group) {
            if (($group['key'] ?? '') === $key) {
                return $group;
            }
        }
        return null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'websiteUrl' => $this->websiteUrl,
            'metadata' => json_encode($this->metadata),
            'vendorMetadata' => json_encode($this->vendorMetadata),
            'features' => $this->features,
            'roles' => $this->roles,
            'groups' => $this->groups,
            'users' => $this->users,
            'tenantId' => $this->tenantId,
            'isLocked' => $this->isLocked,
            'backOfficeEnabled' => $this->backOfficeEnabled,
            'createdAt' => $this->createdAt->format('Y-m-d\TH:i:s.v\Z'),
            'lastLogin' => $this->lastLogin?->format('Y-m-d\TH:i:s.v\Z'),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
