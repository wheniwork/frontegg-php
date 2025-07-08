<?php

namespace Frontegg\Identity;

use Frontegg\Authenticator\FronteggAuthenticator;
use Frontegg\Config\Config;
use Frontegg\Exception\HttpException;
use Frontegg\Exception\Identity\InvalidTokenException;
use Frontegg\Exception\Identity\NoTokenException;
use Frontegg\Exception\Identity\TokenValidationException;
use Frontegg\Exception\UnauthorizedException;
use Frontegg\Http\FronteggHttpClient;
use Frontegg\Identity\Claims\TenantTokenClaims;
use Frontegg\Identity\Claims\TokenClaims;
use Frontegg\Identity\Claims\UserTokenClaims;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Psr\SimpleCache\CacheInterface;
use Firebase\JWT\JWK;

class IdentityManager implements FronteggAuthenticator
{
    private Config $config;
    private FronteggHttpClient $httpClient;
    private ?string $vendorToken = null;
    private ?int $vendorTokenExpiry = null;
    private ?string $userToken = null;
    private ?UserTokenClaims $userTokenClaims = null;
    private ?string $tenantToken = null;
    private ?TenantTokenClaims $tenantTokenClaims = null;
    private ?string $publicKey = null;
    private ?Configuration $jwtConfig = null;
    private ?CacheInterface $cache = null;
    private string $cacheKeyPrefix = 'frontegg_';

    public function __construct(Config $config, FronteggHttpClient $httpClient, ?CacheInterface $cache = null)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    /**
     * Get the JWT Configuration instance
     *
     * @param bool $ignoreCached Whether to ignore the cached configuration
     *
     * @return Configuration
     * @throws HttpException
     */
    private function getJwtConfig(bool $ignoreCached = false): Configuration
    {
        if (!$ignoreCached && $this->jwtConfig !== null) {
            return $this->jwtConfig;
        }

        $publicKey = $this->getPublicKey($ignoreCached);

        try {
            $this->jwtConfig = Configuration::forAsymmetricSigner(
                new Sha256(),
                InMemory::empty(), // We don't need a private key for verification
                InMemory::plainText($publicKey)
            );

            // Add validation constraints
            $this->jwtConfig->setValidationConstraints(
                new SignedWith(
                    $this->jwtConfig->signer(),
                    $this->jwtConfig->verificationKey()
                )
            );

            return $this->jwtConfig;
        } catch (\Exception $e) {
            throw new HttpException('Failed to configure JWT: ' . $e->getMessage());
        }
    }

    /**
     * Get the public key used to verify JWT tokens
     *
     * @param bool $ignoreCached Whether to ignore the cached public key
     * @param string|null $kid The key ID to select from the JWK set (optional)
     *
     * @return string The public key in PEM format
     * @throws HttpException
     */
    public function getPublicKey(bool $ignoreCached = false, ?string $kid = null): string
    {
        $cacheKey = $this->cacheKeyPrefix . 'public_key' . ($kid ? ('_' . $kid) : '');

        if (!$ignoreCached && $this->publicKey !== null && ($kid === null || strpos($cacheKey, $kid) !== false)) {
            return $this->publicKey;
        }

        if (!$ignoreCached && $this->cache !== null) {
            $cachedKey = $this->cache->get($cacheKey);
            if ($cachedKey !== null) {
                $this->publicKey = $cachedKey;
                return $this->publicKey;
            }
        }

        $jwksUrl = rtrim($this->config->getBaseUrl(), '/') . '/.well-known/jwks.json';
        $response = $this->httpClient->get($jwksUrl, [], false, true);

        if (!isset($response['keys']) || !is_array($response['keys']) || count($response['keys']) === 0) {
            throw new HttpException('No keys found in JWKS response');
        }

        // If kid is provided, try to find the key with that kid
        $selectedJwk = null;
        if ($kid !== null) {
            foreach ($response['keys'] as $jwk) {
                if (isset($jwk['kid']) && $jwk['kid'] === $kid) {
                    $selectedJwk = $jwk;
                    break;
                }
            }
        }
        // If not found, fall back to the first key
        if ($selectedJwk === null) {
            $selectedJwk = $response['keys'][0];
        }

        // Parse the selected JWK using firebase/php-jwt
        try {
            // Extract the key ID if available
            $keyId = $selectedJwk['kid'] ?? null;
            $keyData = null;
            
            // Get key material based on key type
            if (isset($selectedJwk['kty']) && $selectedJwk['kty'] === 'RSA') {
                // Validate RSA key has required parameters
                if (!isset($selectedJwk['n']) || !isset($selectedJwk['e'])) {
                    throw new \Exception('RSA key missing required parameters');
                }
                
                // For RSA keys, use the algorithm from the JWK or default to RS256
                $alg = $selectedJwk['alg'] ?? 'RS256';
                $keyData = JWK::parseKey($selectedJwk, $alg);
            } else {
                // For other key types, use the parseKeySet method
                $keys = JWK::parseKeySet(['keys' => [$selectedJwk]]);
                
                // Get the key by ID or first key
                $keyId = $keyId ?? array_key_first($keys);
                if (!isset($keys[$keyId])) {
                    throw new \Exception('Failed to parse JWK');
                }
                
                $keyData = $keys[$keyId];
            }
            
            // In Firebase JWT 6.0+, parseKey/parseKeySet returns a Key object
            if ($keyData instanceof \Firebase\JWT\Key) {
                $keyData = $keyData->getKeyMaterial();
            }
            
            // Convert OpenSSLAsymmetricKey to PEM string if needed
            if ($keyData instanceof \OpenSSLAsymmetricKey || is_resource($keyData)) {
                $details = openssl_pkey_get_details($keyData);
                if ($details === false) {
                    throw new \Exception('Failed to get details from OpenSSL key');
                }
                $publicKey = $details['key'];
            } else {
                $publicKey = $keyData;
            }
            
            // Ensure we have a string
            if (!is_string($publicKey)) {
                throw new \Exception('Failed to convert key to PEM string format');
            }
            
            if (empty($publicKey)) {
                throw new \Exception('Failed to extract public key from JWK');
            }
        } catch (\Exception $e) {
            throw new HttpException('Failed to extract PEM from JWK: ' . $e->getMessage());
        }

        $this->publicKey = $publicKey;
        if ($this->cache !== null) {
            $this->cache->set($cacheKey, $publicKey, 12 * 3600);
        }
        return $this->publicKey;
    }

    /**
     * Get the vendor token for management operations
     *
     * @return string The vendor token
     * @throws HttpException
     */
    public function getVendorToken(): string
    {
        if ($this->vendorToken && $this->vendorTokenExpiry > time()) {
            return $this->vendorToken;
        }

        $response = $this->httpClient->post('/auth/vendor', [
            'json' => [
                'clientId' => $this->config->getClientId(),
                'secret' => $this->config->getApiKey(),
            ],
        ]);

        $this->vendorToken = $response['token'];
        $this->vendorTokenExpiry = time() + ($response['expiresIn'] ?? 3600);

        return $this->vendorToken;
    }

    /**
     * Check if a vendor token is set and valid
     *
     * @return bool True if a valid vendor token is set
     */
    public function hasVendorToken(): bool
    {
        return $this->vendorToken !== null && $this->vendorTokenExpiry > time();
    }

    /**
     * Set the cache implementation to use
     *
     * @param CacheInterface $cache PSR-16 compatible cache implementation
     * @param string $prefix Optional prefix for cache keys
     * @return void
     */
    public function setCache(CacheInterface $cache, string $prefix = 'frontegg_'): void
    {
        $this->cache = $cache;
        $this->cacheKeyPrefix = $prefix;
    }

    /**
     * Set the user token for self-service operations
     *
     * @param string $token The user's JWT token
     *
     * @throws HttpException If token validation fails
     */
    public function setUserToken(string $token): void
    {
        // Validate the token before setting it
        $claims = $this->parseToken($token);
        if (!$claims instanceof UserTokenClaims) {
            throw new HttpException('Invalid user token');
        }
        $this->userToken = $token;
    }

    /**
     * Get the user token for self-service operations
     *
     * @return string The user token
     * @throws NoTokenException if no user token is set
     */
    public function getUserToken(): string
    {
        if (!$this->userToken) {
            throw new NoTokenException('No user token set');
        }

        return $this->userToken;
    }

    /**
     * Get the user token claims
     *
     * @return UserTokenClaims|null
     * @throws NoTokenException if no user token is set
     */
    public function getUserTokenClaims(): ?UserTokenClaims
    {
        if (!$this->userTokenClaims) {
            throw new NoTokenException('No user token set');
        }

        return $this->userTokenClaims;
    }

    /**
     * Check if a user token is set
     *
     * @return bool True if a user token is set
     */
    public function hasUserToken(): bool
    {
        return $this->userToken !== null;
    }

    /**
     * Clear the user token
     */
    public function clearUserToken(): void
    {
        $this->userToken = null;
        $this->userTokenClaims = null;
    }

    public function hasTenantToken(): bool
    {
        return $this->tenantToken !== null;
    }

    /**
     * Get the tenant token for self-service operations
     *
     * @return string The tenant token
     * @throws NoTokenException if no tenant token is set
     */
    public function getTenantToken(): string
    {
        if (!$this->tenantToken) {
            throw new NoTokenException('No tenant token set');
        }

        return $this->tenantToken;
    }

    /**
     * Get the tenant token claims
     *
     * @return TenantTokenClaims|null
     * @throws NoTokenException if no tenant token is set
     */
    public function getTenantTokenClaims(): ?TenantTokenClaims
    {
        if (!$this->tenantTokenClaims) {
            throw new NoTokenException('No tenant token set');
        }

        return $this->tenantTokenClaims;
    }

    /**
     * Get the appropriate token based on context
     *
     * @return string The token to use for authentication
     * @throws NoTokenException if no appropriate token is available
     */
    public function getToken(): string
    {
        if ($this->hasUserToken()) {
            return $this->getUserToken();
        }

        if ($this->hasTenantToken()) {
            return $this->getTenantToken();
        }

        if ($this->hasVendorToken()) {
            return $this->getVendorToken();
        }

        throw new NoTokenException('No authentication token available');
    }

    /**
     * Add a token and automatically determine its type
     * Will set as either vendor token or user token based on validation
     *
     * @param string $token The token to add
     * @param int|null $expiresIn Optional expiry time in seconds (for vendor tokens)
     *
     * @return void
     * @throws InvalidTokenException
     * @throws TokenValidationException
     */
    public function setToken(string $token, ?int $expiresIn = null): void
    {
        $claims = $this->parseToken($token);

        if ($claims instanceof UserTokenClaims) {
            $this->userToken = $token;
            return;
        }

        if ($claims instanceof TenantTokenClaims) {
            $this->tenantToken = $token;
            return;
        }
    }

    /**
     * Parse and validate a JWT token
     *
     * @param string $token The JWT token to parse
     * @return TokenClaims|TenantTokenClaims|UserTokenClaims The parsed token claims
     * @throws InvalidTokenException|TokenValidationException
     */
    public function parseToken(string $token)
    {
        try {
            // Check token length
            if (strlen($token) > 8192) { // Reasonable max length for a JWT
                throw new InvalidTokenException('Invalid JWT format: token too long');
            }

            $config = $this->getJwtConfig();

            try {
                $parsedToken = $config->parser()->parse($token);
            } catch (\Exception $e) {
                throw new InvalidTokenException('Failed to parse token format: ' . $e->getMessage());
            }

            if (!($parsedToken instanceof UnencryptedToken)) {
                throw new InvalidTokenException('Invalid token format: not an unencrypted token');
            }

            // Validate token signature
            $constraints = [
                new SignedWith(
                    $config->signer(),
                    $config->verificationKey()
                )
            ];

            try {
                if (!$config->validator()->validate($parsedToken, ...$constraints)) {
                    throw new TokenValidationException('Token signature verification failed');
                }
            } catch (TokenValidationException $e) {
                throw $e;
            } catch (\Exception $e) {
                throw new TokenValidationException('Token validation error: ' . $e->getMessage());
            }

            // First parse as base claims to get the type
            try {
                $baseClaims = new TokenClaims($parsedToken);
            } catch (\Exception $e) {
                throw new InvalidTokenException('Failed to parse token claims: ' . $e->getMessage());
            }

            // Then create the appropriate claims object based on type
            if ($this->isUserToken($baseClaims)) {
                $this->userTokenClaims = new UserTokenClaims($parsedToken);
                return $this->userTokenClaims;
            } elseif ($this->isTenantToken($baseClaims)) {
                $this->tenantTokenClaims = new TenantTokenClaims($parsedToken);
                return $this->tenantTokenClaims;
            }

            return $baseClaims;
        } catch (InvalidTokenException|TokenValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new InvalidTokenException('Failed to process token: ' . $e->getMessage());
        }
    }

    /**
     * Check if a token is a user token (either regular user or user API token)
     *
     * @param TokenClaims $baseClaims The JWT token to check
     *
     * @return bool True if the token is a user token
     * @throws InvalidTokenException If token parsing fails
     */
    private function isUserToken(TokenClaims $baseClaims): bool
    {
        try {
            $userTokenTypes = ['userToken', 'userApiToken'];
            return in_array($baseClaims->getType(), $userTokenTypes);
        } catch (InvalidTokenException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new InvalidTokenException('Failed to check token type: ' . $e->getMessage());
        }
    }

    /**
     * Check if a token is a tenant token
     *
     * @param TokenClaims $baseClaims The JWT token to check
     *
     * @return bool True if the token is a tenant token
     * @throws InvalidTokenException If token parsing fails
     */
    private function isTenantToken(TokenClaims $baseClaims): bool
    {
        try {
            $tenantTokenTypes = ['tenantApiToken'];
            return in_array($baseClaims->getType(), $tenantTokenTypes);
        } catch (InvalidTokenException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new InvalidTokenException('Failed to check token type: ' . $e->getMessage());
        }
    }

    /**
     * Parse a token and return it as a UserTokenClaims if it is a user token
     *
     * @param string $token The JWT token to parse
     *
     * @return UserTokenClaims The parsed user token claims
     * @throws InvalidTokenException If token is not a user token or parsing fails
     */
    public function parseUserToken(string $token): UserTokenClaims
    {
        $claims = $this->parseToken($token);
        if (!$claims instanceof UserTokenClaims) {
            throw new InvalidTokenException('Token is not a user token');
        }

        return $claims;
    }

    /**
     * Parse a token and return it as a TenantTokenClaims if it is a tenant token
     *
     * @param string $token The JWT token to parse
     *
     * @return TenantTokenClaims The parsed tenant token claims
     * @throws InvalidTokenException If token is not a tenant token or parsing fails
     */
    public function parseTenantToken(string $token): TenantTokenClaims
    {
        $claims = $this->parseToken($token);
        if (!$claims instanceof TenantTokenClaims) {
            throw new InvalidTokenException('Token is not a tenant token');
        }

        return $claims;
    }
}
