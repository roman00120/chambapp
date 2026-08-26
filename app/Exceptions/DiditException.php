<?php

namespace App\Exceptions;

use RuntimeException;

class DiditException extends RuntimeException
{
    public function __construct(
        public readonly string $reason,
        public readonly int $httpStatus = 502,
        public readonly bool $retryable = false,
    ) {
        parent::__construct($reason);
    }
}
