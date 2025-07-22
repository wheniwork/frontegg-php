<?php

namespace Frontegg\Entities\Roles\Exception;

use Frontegg\Exception\HttpException;

class RolesNotFoundException extends HttpException
{
    public function __construct(string $message = 'No roles found', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
