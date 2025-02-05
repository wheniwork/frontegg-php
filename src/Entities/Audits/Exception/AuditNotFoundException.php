<?php

namespace Frontegg\Entities\Audits\Exception;

use Frontegg\Exception\HttpException;

class AuditNotFoundException extends HttpException
{
    public function __construct(string $message = 'Audit not found', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
