<?php

namespace Frontegg\Entities\Roles\Exception;

use Frontegg\Exception\HttpException;

class RoleAlreadyExistsException extends HttpException
{
    public function __construct(string $message = 'Role already exists', int $code = 409)
    {
        parent::__construct($message, $code);
    }
}
