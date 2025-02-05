<?php

namespace Frontegg\Exception;

class ValidationException extends HttpException
{
    public function __construct(string $message = 'Validation error', int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
