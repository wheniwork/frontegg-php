<?php

namespace Frontegg\Entities\Users\Exception;

use Frontegg\Exception\ValidationException;

class UserValidationException extends ValidationException
{
    public function __construct(string $message = 'Invalid user data', int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
