<?php

namespace Frontegg\Entities\Audits;

use DateTimeImmutable;
use JsonSerializable;

class Audit implements JsonSerializable
{
    private string $id;
    private string $tenantId;
    private ?string $userId;
    private string $type;
    private string $category;
    private string $severity;
    private array $data;
    private array $metadata;
    private ?string $ip;
    private ?string $userAgent;
    private ?string $origin;
    private DateTimeImmutable $createdAt;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->tenantId = $data['tenantId'];
        $this->userId = $data['userId'] ?? null;
        $this->type = $data['type'];
        $this->category = $data['category'];
        $this->severity = $data['severity'];
        $this->data = $data['data'] ?? [];
        $this->metadata = $data['metadata'] ?? [];
        $this->ip = $data['ip'] ?? null;
        $this->userAgent = $data['userAgent'] ?? null;
        $this->origin = $data['origin'] ?? null;
        $this->createdAt = new DateTimeImmutable($data['createdAt']);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenantId' => $this->tenantId,
            'userId' => $this->userId,
            'type' => $this->type,
            'category' => $this->category,
            'severity' => $this->severity,
            'data' => $this->data,
            'metadata' => $this->metadata,
            'ip' => $this->ip,
            'userAgent' => $this->userAgent,
            'origin' => $this->origin,
            'createdAt' => $this->createdAt->format('Y-m-d\TH:i:s.v\Z'),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
