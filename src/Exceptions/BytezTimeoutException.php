<?php

namespace Bytez\LaravelBytez\Exceptions;

class BytezTimeoutException extends BytezException
{
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
