<?php

namespace Frontegg\Entities\Audits\Exception;

use Frontegg\Exception\ValidationException;

class AuditValidationException extends ValidationException
{
    public function __construct(string $message = 'Invalid audit data', int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
