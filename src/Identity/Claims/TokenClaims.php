<?php

namespace Frontegg\Identity\Claims;

use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Token\RegisteredClaims;
use JsonSerializable;
use DateTimeImmutable;

/**
 * Base class for Frontegg JWT token claims
 * 
 * This class provides the foundation for handling JWT token claims in the Frontegg authentication system.
 * It extracts and manages both standard JWT claims and Frontegg-specific claims from the token.
 */
class TokenClaims implements JsonSerializable
{
    /** @var UnencryptedToken The underlying JWT token */
    protected UnencryptedToken $token;

    /** @var string The type of token (e.g., 'user', 'tenant') */
    protected string $type;

    /** @var array<string, mixed> Additional metadata associated with the token */
    protected array $metadata;

    /** @var string ID of the tenant this token belongs to */
    protected string $tenantId;

    /** @var string ID of the application this token is valid for */
    protected string $applicationId;

    /** @var array<string> List of permissions granted by this token */
    protected array $permissions;

    /** @var array<string> List of roles assigned to this token */
    protected array $roles;

    /**
     * Creates a new TokenClaims instance from a JWT token
     *
     * @param UnencryptedToken $token The decoded JWT token containing the claims
     */
    public function __construct(UnencryptedToken $token)
    {
        $this->token = $token;
        $claims = $token->claims();
        
        $this->type = $claims->get('type', '');
        $this->metadata = $claims->get('metadata', []);
        $this->tenantId = $claims->get('tenantId', '');
        $this->applicationId = $claims->get('applicationId', '');
        $this->permissions = array_map('strval', $claims->get('permissions', []));
        $this->roles = array_map('strval', $claims->get('roles', []));
    }

    /**
     * Gets the token type
     *
     * @return string The type of token
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Gets the token metadata
     *
     * @return array<string, mixed> Additional metadata associated with the token
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Gets the tenant ID
     *
     * @return string The ID of the tenant this token belongs to
     */
    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    /**
     * Gets the application ID
     *
     * @return string The ID of the application this token is valid for
     */
    public function getApplicationId(): string
    {
        return $this->applicationId;
    }

    /**
     * Gets the list of permissions
     *
     * @return array<string> List of permissions granted by this token
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Gets the list of roles
     *
     * @return array<string> List of roles assigned to this token
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Gets the token issuer
     *
     * @return string The issuer of the token
     */
    public function getIssuer(): string
    {
        return $this->token->claims()->get(RegisteredClaims::ISSUER, '');
    }

    /**
     * Gets the token subject
     *
     * @return string The subject of the token
     */
    public function getSubject(): string
    {
        return $this->token->claims()->get(RegisteredClaims::SUBJECT, '');
    }

    /**
     * Gets the token audience
     *
     * @return array<string> The intended audience of the token
     */
    public function getAudience(): array
    {
        return $this->token->claims()->get(RegisteredClaims::AUDIENCE, []);
    }

    /**
     * Gets the token expiration time
     *
     * @return DateTimeImmutable|null The expiration time of the token
     */
    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->token->claims()->get(RegisteredClaims::EXPIRATION_TIME);
    }

    /**
     * Gets the token "not before" time
     *
     * @return DateTimeImmutable|null The time before which the token is not valid
     */
    public function getNotBefore(): ?DateTimeImmutable
    {
        return $this->token->claims()->get(RegisteredClaims::NOT_BEFORE);
    }

    /**
     * Gets the token issued at time
     *
     * @return DateTimeImmutable|null The time at which the token was issued
     */
    public function getIssuedAt(): ?DateTimeImmutable
    {
        return $this->token->claims()->get(RegisteredClaims::ISSUED_AT);
    }

    /**
     * Gets the token ID
     *
     * @return string The unique identifier for the token
     */
    public function getId(): string
    {
        return $this->token->claims()->get(RegisteredClaims::ID, '');
    }

    /**
     * Checks if the token has expired
     *
     * @return bool True if the token has expired, false otherwise
     */
    public function hasExpired(): bool
    {
        $expiresAt = $this->getExpiresAt();
        return $expiresAt !== null && $expiresAt < new DateTimeImmutable();
    }

    /**
     * Converts the claims to a JSON-serializable array
     *
     * @return array<string, mixed> Array representation of all claims
     */
    public function jsonSerialize(): array
    {
        return [
            // Frontegg-specific claims
            'type' => $this->type,
            'metadata' => $this->metadata,
            'tenantId' => $this->tenantId,
            'applicationId' => $this->applicationId,
            'permissions' => $this->permissions,
            'roles' => $this->roles,
            
            // Standard JWT claims
            'iss' => $this->getIssuer(),
            'sub' => $this->getSubject(),
            'aud' => $this->getAudience(),
            'exp' => $this->getExpiresAt()?->getTimestamp(),
            'nbf' => $this->getNotBefore()?->getTimestamp(),
            'iat' => $this->getIssuedAt()?->getTimestamp(),
            'jti' => $this->getId(),
        ];
    }
}
