<?php

namespace Frontegg\Exception;

class UnauthorizedException extends HttpException
{
    public function __construct(string $message = 'Unauthorized', int $code = 401)
    {
        parent::__construct($message, $code);
    }
}
