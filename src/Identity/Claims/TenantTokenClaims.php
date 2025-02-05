<?php

namespace Frontegg\Identity\Claims;

use Lcobucci\JWT\UnencryptedToken;

/**
 * Represents the claims contained within a Frontegg tenant JWT token
 * 
 * This class extends the base TokenClaims to handle tenant-specific claims,
 * particularly focusing on tenant administration and management information.
 */
class TenantTokenClaims extends TokenClaims
{
    private const CLAIM_CREATED_BY_USER_ID = 'createdByUserId';

    /**
     * Creates a new TenantTokenClaims instance from a JWT token
     *
     * @param UnencryptedToken $token The decoded JWT token containing tenant claims
     */
    public function __construct(UnencryptedToken $token)
    {
        parent::__construct($token);
    }

    /**
     * Gets the ID of the user who created this tenant token
     *
     * @return string The user ID of the token creator
     */
    public function getCreatedByUserId(): string
    {
        return $this->token->claims()->get(self::CLAIM_CREATED_BY_USER_ID, '');
    }

    /**
     * Converts the claims to a JSON-serializable array
     *
     * @return array<string, mixed> Array representation of all claims including tenant-specific ones
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            self::CLAIM_CREATED_BY_USER_ID => $this->getCreatedByUserId(),
        ]);
    }
}
