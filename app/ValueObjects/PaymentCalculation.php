<?php

namespace App\ValueObjects;

final readonly class PaymentCalculation
{
    public function __construct(
        public string $grossAmount,
        public string $platformFeePercent,
        public string $platformFee,
        public string $professionalAmount,
        public string $currency,
    ) {}
}
