<?php

namespace App\Exceptions;

use Exception;

class ServiceException extends Exception
{
    protected array $params = [];

    public function __construct(string $message, int $code = 422, array $params = [], Exception $previous = null)
    {
        $this->params = $params;
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        return response()->json([
            'message' => __($this->getMessage(), $this->params),
            'data' => null,
        ], $this->getCode() ?: 422);
    }
}
