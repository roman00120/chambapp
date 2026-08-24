<?php

namespace App\ValueObjects;

final readonly class PaymentCalculation
{
    public string $grossAmount;
    public string $platformFeePercent;
    public string $platformFee;
    public string $professionalAmount;

    public function __construct(
        public string $economicModelVersion,
        public string $baseAmount,
        public string $clientServiceFeePercent,
        public string $clientServiceFee,
        public string $professionalCommissionPercent,
        public string $professionalCommission,
        public string $customerTotal,
        public string $platformGrossFee,
        public string $professionalAmountBeforeExternalCosts,
        public string $currency,
    ) {
        // Deprecated aliases retained while historical API consumers migrate.
        $this->grossAmount = $customerTotal;
        $this->platformFeePercent = $this->aggregatePercent();
        $this->platformFee = $platformGrossFee;
        $this->professionalAmount = $professionalAmountBeforeExternalCosts;
    }

    private function aggregatePercent(): string
    {
        $client = (int) str_replace('.', '', $this->clientServiceFeePercent);
        $professional = (int) str_replace('.', '', $this->professionalCommissionPercent);
        $units = $client + $professional;

        return intdiv($units, 100).'.'.str_pad((string) ($units % 100), 2, '0', STR_PAD_LEFT);
    }
}
