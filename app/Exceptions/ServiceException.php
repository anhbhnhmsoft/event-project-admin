<?php

namespace App\Exceptions;

use Exception;

class ServiceException extends Exception
{
    public function __construct(string $message, int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
