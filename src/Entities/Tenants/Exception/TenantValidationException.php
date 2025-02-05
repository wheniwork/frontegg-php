<?php

namespace Frontegg\Entities\Tenants\Exception;

use Frontegg\Exception\ValidationException;

class TenantValidationException extends ValidationException
{
    public function __construct(string $message = 'Invalid tenant data', int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
