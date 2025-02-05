<?php

namespace Frontegg\Clients;

use Frontegg\Exception\HttpException;
use Frontegg\Exception\UnauthorizedException;

/**
 * Trait for vendor operations that require vendor authentication
 */
trait VendorClientTrait
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
    protected function getVendorHeaders(array $additionalHeaders = []): array
    {
        $this->requireVendorToken();
        return array_merge(
            [
                'Authorization' => 'Bearer ' . $this->authenticator->getVendorToken(),
                'x-client-id' => $this->config->getClientId(),
            ],
            $additionalHeaders
        );
    }
}
