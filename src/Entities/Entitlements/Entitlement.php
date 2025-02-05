<?php

namespace Frontegg\Entities\Entitlements;

use DateTimeImmutable;
use JsonSerializable;

class Entitlement implements JsonSerializable
{
    private string $id;
    private string $planId;
    private string $assignLevel;
    private ?string $userId;
    private ?string $tenantId;
    private ?DateTimeImmutable $expirationDate;
    private ?DateTimeImmutable $createdAt;
    private ?array $plan;
    private ?array $user;
    private ?array $tenant;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->planId = $data['planId'];
        $this->assignLevel = $data['assignLevel'];
        $this->userId = $data['userId'] ?? null;
        $this->tenantId = $data['tenantId'] ?? null;
        $this->expirationDate = isset($data['expirationDate']) 
            ? new DateTimeImmutable($data['expirationDate'])
            : null;
        $this->createdAt = isset($data['createdAt'])
            ? new DateTimeImmutable($data['createdAt'])
            : null;
        $this->plan = $data['plan'] ?? null;
        $this->user = $data['user'] ?? null;
        $this->tenant = $data['tenant'] ?? null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPlanId(): string
    {
        return $this->planId;
    }

    public function getAssignLevel(): string
    {
        return $this->assignLevel;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function getExpirationDate(): ?DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPlan(): ?array
    {
        return $this->plan;
    }

    public function getUser(): ?array
    {
        return $this->user;
    }

    public function getTenant(): ?array
    {
        return $this->tenant;
    }

    public function isExpired(): bool
    {
        if ($this->expirationDate === null) {
            return false;
        }
        return $this->expirationDate < new DateTimeImmutable();
    }

    public function isUserLevel(): bool
    {
        return $this->assignLevel === 'USER';
    }

    public function isTenantLevel(): bool
    {
        return $this->assignLevel === 'TENANT';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'planId' => $this->planId,
            'assignLevel' => $this->assignLevel,
            'userId' => $this->userId,
            'tenantId' => $this->tenantId,
            'expirationDate' => $this->expirationDate?->format('Y-m-d\TH:i:s.v\Z'),
            'createdAt' => $this->createdAt?->format('Y-m-d\TH:i:s.v\Z'),
            'plan' => $this->plan,
            'user' => $this->user,
            'tenant' => $this->tenant,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
