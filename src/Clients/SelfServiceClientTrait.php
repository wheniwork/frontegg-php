<?php

namespace Frontegg\Clients;

use Frontegg\Exception\UnauthorizedException;

/**
 * Trait for self-service operations that require user authentication
 */
trait SelfServiceClientTrait
{
    /**
     * Ensure the client is authenticated with a user token
     *
     * @throws UnauthorizedException
     */
    protected function requireUserToken(): void
    {
        if (!$this->authenticator->hasUserToken()) {
            throw new UnauthorizedException('This operation requires user authentication. Call setUserToken() first.');
        }
    }

    /**
     * Get headers with user authentication
     *
     * @param array $additionalHeaders Additional headers to include
     * @return array Headers with user authentication
     * @throws UnauthorizedException if no user token is set
     */
    protected function getUserHeaders(array $additionalHeaders = []): array
    {
        $this->requireUserToken();
        return array_merge(
            [
                'Authorization' => 'Bearer ' . $this->authenticator->getUserToken(),
                'x-client-id' => $this->config->getClientId(),
            ],
            $additionalHeaders
        );
    }
}
