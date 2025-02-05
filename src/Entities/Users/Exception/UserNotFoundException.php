<?php

namespace Frontegg\Entities\Users\Exception;

use Frontegg\Exception\HttpException;

class UserNotFoundException extends HttpException
{
    public function __construct(string $message = 'User not found', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
