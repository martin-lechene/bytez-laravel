<?php

namespace Bytez\LaravelBytez\Exceptions;

class BytezRateLimitException extends BytezException
{
    protected ?int $retryAfter = null;

    public function __construct(string $message = "", int $code = 429, ?\Throwable $previous = null, ?int $retryAfter = null)
    {
        parent::__construct($message, $code, $previous);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
