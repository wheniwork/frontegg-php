<?php

namespace Frontegg\Clients\Management;

use Frontegg\Clients\BaseClient;
use Frontegg\Exception\UnauthorizedException;

abstract class BaseManagementClient extends BaseClient
{
    /**
     * Ensure the client is authenticated with a vendor token
     *
     * @throws UnauthorizedException
     */
    protected function requireVendorToken(): void
    {
        if (!$this->authenticator->hasVendorToken()) {
            throw new UnauthorizedException('This operation requires vendor authentication');
        }
    }

    /**
     * Get headers with vendor authentication
     *
     * @param array $additionalHeaders Additional headers to include
     * @return array Headers with vendor authentication
     * @throws UnauthorizedException if no vendor token is set
     */
    protected function getHeaders(array $additionalHeaders = []): array
    {
        $this->requireVendorToken();
        return array_merge(
            [
                'Authorization' => 'Bearer ' . $this->authenticator->getVendorToken(),
            ],
            $additionalHeaders
        );
    }
}
