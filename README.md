# Frontegg PHP

This is a PHP client for the [Frontegg](https://frontegg.com) API.

## Installation

```bash
composer require wheniwork/frontegg-php
```

## Usage

```php
use Frontegg\Client;
use Frontegg\Config\FronteggConfig;

// Initialize the client
$config = new FronteggConfig([
    'clientId' => 'your-client-id',
    'apiKey' => 'your-api-key',
    // Optional configurations
    'region' => 'us', // default
    'endpoint' => 'https://api.us.frontegg.com', // default
    'httpOptions' => [] // Additional Guzzle options
]);

$frontegg = new Client($config);

// Example: Self-service operations (using user token)
// The identity manager will automatically detect the token type
$frontegg->identity()->addToken('your-jwt-token');
$frontegg->authenticate('your-jwt-token'); // is just a convenience method that calls addToken

// Now use self-service operations
$profile = $frontegg->selfService()->users()->getProfile();

// Example: Parse and validate a JWT token
$token = 'your-jwt-token';
try {
    // The identity manager will automatically fetch and validate tokens
    // using the JWKS endpoint (/.well-known/jwks.json)
    $identityManager = $frontegg->identity();
    
    // Add and validate the token
    $identityManager->addToken($token);
    
    // Get the parsed claims
    $claims = $identityManager->parseToken($token);
    
    // Access claims based on token type
    if ($claims instanceof UserTokenClaims) {
        echo "User: " . $claims->getName();
        echo "Email: " . $claims->getEmail();
    } elseif ($claims instanceof TenantTokenClaims) {
        echo "Created by user: " . $claims->getCreatedByUserId();
    }
    
    // Common claims available on all token types
    echo "Tenant ID: " . $claims->getTenantId();
    echo "Permissions: " . implode(", ", $claims->getPermissions());
} catch (\Frontegg\Exception\HttpException $e) {
    echo "Token validation failed: " . $e->getMessage();
}

// Example: Management operations (using vendor token)
$users = $frontegg->management()->users()->getUsers();

// Example: Parse and validate a JWT token
$token = 'your-jwt-token';
try {
    // The identity manager will automatically fetch and validate tokens
    // using the JWKS endpoint (/.well-known/jwks.json)
    $identityManager = $frontegg->identity();
    
    // Add and validate the token
    $identityManager->addToken($token);
    
    // Get the parsed claims
    $claims = $identityManager->parseToken($token);
    
    // Access claims based on token type
    if ($claims instanceof UserTokenClaims) {
        echo "User: " . $claims->getName();
        echo "Email: " . $claims->getEmail();
    } elseif ($claims instanceof TenantTokenClaims) {
        echo "Created by user: " . $claims->getCreatedByUserId();
    }
    
    // Common claims available on all token types
    echo "Tenant ID: " . $claims->getTenantId();
    echo "Permissions: " . implode(", ", $claims->getPermissions());
} catch (\Frontegg\Exception\HttpException $e) {
    echo "Token validation failed: " . $e->getMessage();
}
```

## Configuration

You can configure the client using environment variables:
- `FRONTEGG_CLIENT_ID`: Your Frontegg Client ID
- `FRONTEGG_API_KEY`: Your Frontegg API Key

Or pass them directly to the `FronteggConfig` constructor as shown in the usage example.

## Authentication

The SDK supports two types of authentication:

### Vendor Authentication
Used for management operations. The SDK automatically handles vendor token acquisition and renewal:
```php
// Uses vendor token automatically
$users = $frontegg->management()->users()->getUsers();
$tenants = $frontegg->management()->tenants()->getTenants();
```

### User Authentication
Required for self-service operations. You can add any JWT token and the SDK will automatically determine its type:
```php
// Add a token (automatically detects type and validates)
$frontegg->identity()->addToken($userJwtToken);

// Now you can use self-service operations
$profile = $frontegg->selfService()->users()->getProfile();

// Check if authenticated
if ($frontegg->identity()->hasToken()) {
    // Do something
}

// Clear token when needed
$frontegg->identity()->clearToken();
```

## Token Validation

The SDK automatically validates JWT tokens using Frontegg's JWKS endpoint (/.well-known/jwks.json). This provides several benefits:

1. **Automatic Key Rotation**: The SDK fetches the latest public keys from Frontegg's JWKS endpoint
2. **Standards Compliance**: Uses standard JWT validation practices with RSA public keys
3. **Security**: Validates token signatures and claims according to JWT standards
4. **Automatic Type Detection**: Determines if a token is a user token, tenant token, or vendor token

```php
// The identity manager handles token validation automatically
$identityManager = $frontegg->identity();

// Add a token (automatically validates and determines type)
$identityManager->addToken($token);

// Get the current token
$token = $identityManager->getToken();

// Validate a token without storing it
if ($identityManager->validateToken($token)) {
    echo "Token is valid";
}
```

## Available Clients

The SDK provides access to various Frontegg services through dedicated clients:

### Management Clients
- `users()`: User management operations
- `tenants()`: Tenant management operations
- `roles()`: Role management operations
- `permissions()`: Permission management operations
- `events()`: Event management operations
- `audits()`: Audit log operations
- `groups()`: Group management operations

### Self-Service Clients
- `users()`: User self-service operations
- `tenants()`: Tenant self-service operations
- `sso()`: SSO configuration operations
- `events()`: Event reporting operations

### Identity Manager
The `identity()` client provides token management and validation:
- Token parsing and validation
- JWKS-based signature verification
- Automatic token type detection
- Token claims access with type safety

## Token Claims

The SDK provides strongly-typed access to token claims through dedicated classes:
- `TokenClaims`: Base claims (type, metadata, permissions, etc.)
- `UserTokenClaims`: User-specific claims (name, email, etc.)
- `TenantTokenClaims`: Tenant-specific claims (createdByUserId)

## Error Handling

The SDK uses custom exceptions for error handling:

- `HttpException`: Base exception for all HTTP-related errors
  - `UnauthorizedException`: Authentication/authorization errors
  - `ValidationException`: Input validation errors
  - `NotFoundException`: Resource not found
  - `RateLimitException`: API rate limit exceeded

Example:
```php
try {
    $users = $frontegg->management()->users()->getUsers();
} catch (UnauthorizedException $e) {
    // Handle authentication errors
    echo "Authentication failed: " . $e->getMessage();
} catch (HttpException $e) {
    // Handle other API errors
    echo "API error: " . $e->getMessage();
}
