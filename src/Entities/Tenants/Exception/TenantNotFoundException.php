<?php

namespace Frontegg\Entities\Tenants\Exception;

use Frontegg\Exception\HttpException;

class TenantNotFoundException extends HttpException
{
    public function __construct(string $message = 'Tenant not found', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
