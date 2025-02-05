<?php

namespace Frontegg\Authenticator;

interface FronteggAuthenticator
{
    /**
     * Get the appropriate token based on context
     *
     * @return string The token to use for authentication
     * @throws \Frontegg\Exception\Identity\NoTokenException if no appropriate token is available
     */
    public function getToken(): string;

    /**
     * Check if a vendor token is set and valid
     *
     * @return bool True if a valid vendor token is set
     */
    public function hasVendorToken(): bool;

    /**
     * Check if a user token is set
     *
     * @return bool True if a user token is set
     */
    public function hasUserToken(): bool;
}
