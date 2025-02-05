<?php

namespace Frontegg\Clients\SelfService;

use Frontegg\Clients\BaseClient;
use Frontegg\Exception\UnauthorizedException;

abstract class BaseSelfServiceClient extends BaseClient
{
    /**
     * Ensure the client is authenticated with a user token
     *
     * @throws UnauthorizedException
     */
    protected function requireUserToken(): void
    {
        if (!$this->identityManager->hasUserToken()) {
            throw new UnauthorizedException('This operation requires user authentication');
        }
    }

    /**
     * Get headers with user authentication
     *
     * @param array $additionalHeaders Additional headers to include
     * @return array Headers with user authentication
     * @throws UnauthorizedException if no user token is set
     */
    protected function getHeaders(array $additionalHeaders = []): array
    {
        $this->requireUserToken();
        return array_merge(
            [
                'Authorization' => 'Bearer ' . $this->identityManager->getUserToken(),
            ],
            $additionalHeaders
        );
    }
}
