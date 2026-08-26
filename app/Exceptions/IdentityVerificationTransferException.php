<?php

namespace App\Exceptions;

use RuntimeException;

class IdentityVerificationTransferException extends RuntimeException
{
    public function __construct(
        public readonly string $reason,
        public readonly int $httpStatus,
    ) {
        parent::__construct($reason);
    }
}
