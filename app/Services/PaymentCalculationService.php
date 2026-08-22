<?php

namespace App\Services;

use App\ValueObjects\PaymentCalculation;
use DomainException;

class PaymentCalculationService
{
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

        return new PaymentCalculation(
            $this->formatUnits($grossCents),
            $this->formatUnits($percentUnits),
            $this->formatUnits($feeCents),
            $this->formatUnits($professionalCents),
            $currency,
        );
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
}
