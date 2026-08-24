<?php

namespace App\Services;

use App\Models\JobRequest;
use App\ValueObjects\PaymentCalculation;
use DomainException;

class PaymentCalculationService
{
    /**
     * Legacy single-fee calculation retained for tips, commerce and V1 records.
     */
    public function calculate(string|int $grossAmount, ?string $platformFeePercent = null): PaymentCalculation
    {
        $currency = (string) config('chambapp.payments.currency', 'MXN');
        $percent = $platformFeePercent ?? (string) config('chambapp.payments.platform_fee_percent', '15');
        $grossCents = $this->parseUnits((string) $grossAmount, 2);
        $percentUnits = $this->parseUnits($percent, 2);

        if ($grossCents <= 0 || $percentUnits < 0 || $percentUnits > 10000) {
            throw new DomainException('El monto o la comisión no son válidos.');
        }

        $feeCents = intdiv(($grossCents * $percentUnits) + 5000, 10000);
        $professionalCents = $grossCents - $feeCents;

        $gross = $this->formatUnits($grossCents);
        $percent = $this->formatUnits($percentUnits);
        $fee = $this->formatUnits($feeCents);
        $professional = $this->formatUnits($professionalCents);

        return new PaymentCalculation('single_platform_fee_15', $gross, '0.00', '0.00', $percent, $fee, $gross, $fee, $professional, $currency);
    }

    public function calculateJob(
        string|int $baseAmount,
        ?string $clientServiceFeePercent = null,
        ?string $professionalCommissionPercent = null,
    ): PaymentCalculation {
        $currency = (string) config('chambapp.payments.currency', 'MXN');
        $clientPercent = $clientServiceFeePercent ?? (string) config('chambapp.payments.client_service_fee_percent', '15');
        $professionalPercent = $professionalCommissionPercent ?? (string) config('chambapp.payments.professional_commission_percent', '15');
        $baseCents = $this->parseUnits((string) $baseAmount, 2);
        $clientPercentUnits = $this->parseUnits($clientPercent, 2);
        $professionalPercentUnits = $this->parseUnits($professionalPercent, 2);

        if ($baseCents <= 0
            || $clientPercentUnits < 0 || $clientPercentUnits > 10000
            || $professionalPercentUnits < 0 || $professionalPercentUnits > 10000) {
            throw new DomainException('El monto o las comisiones no son válidos.');
        }

        $clientFeeCents = $this->percentageOf($baseCents, $clientPercentUnits);
        $professionalFeeCents = $this->percentageOf($baseCents, $professionalPercentUnits);
        $professionalCents = $baseCents - $professionalFeeCents;

        return new PaymentCalculation(
            'client_15_professional_15',
            $this->formatUnits($baseCents),
            $this->formatUnits($clientPercentUnits),
            $this->formatUnits($clientFeeCents),
            $this->formatUnits($professionalPercentUnits),
            $this->formatUnits($professionalFeeCents),
            $this->formatUnits($baseCents + $clientFeeCents),
            $this->formatUnits($clientFeeCents + $professionalFeeCents),
            $this->formatUnits($professionalCents),
            $currency,
        );
    }

    public function forJob(JobRequest $job): PaymentCalculation
    {
        if ($job->economic_model_version === 'client_15_professional_15' && $job->base_amount !== null) {
            return new PaymentCalculation(
                (string) $job->economic_model_version,
                (string) $job->base_amount,
                (string) $job->client_service_fee_percent,
                (string) $job->client_service_fee,
                (string) $job->professional_commission_percent,
                (string) $job->professional_commission,
                (string) $job->customer_total,
                (string) $job->platform_gross_fee,
                (string) $job->professional_amount_before_external_costs,
                (string) config('chambapp.payments.currency', 'MXN'),
            );
        }

        return $this->calculate((string) $job->agreed_price);
    }

    public function sameAmount(string|int $left, string|int $right): bool
    {
        return $this->parseUnits((string) $left, 2) === $this->parseUnits((string) $right, 2);
    }

    public function normalize(string|int $amount): string
    {
        return $this->formatUnits($this->parseUnits((string) $amount, 2));
    }

    public function sum(iterable $amounts): string
    {
        $total = 0;
        foreach ($amounts as $amount) {
            $total += $this->parseUnits((string) $amount, 2);
        }

        return $this->formatUnits($total);
    }

    public function isAtMost(string|int $amount, string|int $maximum): bool
    {
        return $this->parseUnits((string) $amount, 2) <= $this->parseUnits((string) $maximum, 2);
    }

    public function formatAmount(string|int $amount): string
    {
        [$whole, $cents] = explode('.', $this->normalize($amount));

        return '$'.number_format((int) $whole, 0, '.', ',').'.'.$cents.' MXN';
    }

    private function parseUnits(string $amount, int $scale): int
    {
        $normalized = str_replace(',', '.', trim($amount));
        if (! preg_match('/^\d+(?:\.\d{1,'.$scale.'})?$/', $normalized)) {
            throw new DomainException('El monto debe ser un número decimal válido.');
        }

        [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '');
        $fraction = str_pad($fraction, $scale, '0');

        return ((int) $whole * (10 ** $scale)) + (int) $fraction;
    }

    private function formatUnits(int $units): string
    {
        return intdiv($units, 100).'.'.str_pad((string) ($units % 100), 2, '0', STR_PAD_LEFT);
    }

    private function percentageOf(int $amountUnits, int $percentUnits): int
    {
        return intdiv(($amountUnits * $percentUnits) + 5000, 10000);
    }
}
