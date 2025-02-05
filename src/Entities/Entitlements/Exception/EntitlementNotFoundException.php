<?php

namespace Frontegg\Entities\Entitlements\Exception;

use Frontegg\Exception\HttpException;

class EntitlementNotFoundException extends HttpException
{
    public function __construct(string $message = 'Entitlement not found', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
