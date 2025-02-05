<?php

namespace Frontegg\Entities\Entitlements\Exception;

use Frontegg\Exception\ValidationException;

class EntitlementValidationException extends ValidationException
{
    public function __construct(string $message = 'Invalid entitlement data', int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
