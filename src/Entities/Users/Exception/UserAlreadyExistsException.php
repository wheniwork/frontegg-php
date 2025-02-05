<?php

namespace Frontegg\Entities\Users\Exception;

use Frontegg\Exception\HttpException;

class UserAlreadyExistsException extends HttpException
{
    public function __construct(string $message = 'User already exists', int $code = 409)
    {
        parent::__construct($message, $code);
    }
}
