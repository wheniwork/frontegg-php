<?php

namespace Frontegg\Entities\Roles\Exception;

use Frontegg\Exception\HttpException;

class RoleValidationException extends HttpException
{
    public function __construct(string $message = 'Invalid role data', int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
