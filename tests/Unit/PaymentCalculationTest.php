<?php

namespace Tests\Unit;

use App\Services\PaymentCalculationService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PaymentCalculationTest extends TestCase
{
    #[DataProvider('calculationProvider')]
    public function test_platform_fee_is_calculated_in_cents(string $gross, string $fee, string $professional): void
    {
        $calculation = (new PaymentCalculationService)->calculate($gross);

        $this->assertSame($gross, $calculation->grossAmount);
        $this->assertSame('15.00', $calculation->platformFeePercent);
        $this->assertSame($fee, $calculation->platformFee);
        $this->assertSame($professional, $calculation->professionalAmount);
    }

    public function test_gross_amount_is_exactly_fee_plus_professional_amount(): void
    {
        $service = new PaymentCalculationService;
        $calculation = $service->calculate('1234.56');

        $this->assertSame('1234.56', $calculation->grossAmount);
        $this->assertSame('1234.56', $this->addMoney($calculation->platformFee, $calculation->professionalAmount));
    }

    #[DataProvider('dualFeeProvider')]
    public function test_dual_fee_model_uses_base_price_and_deterministic_cents(
        string $base,
        string $fee,
        string $customerTotal,
        string $professionalAmount,
        string $platformGross,
    ): void {
        $calculation = (new PaymentCalculationService)->calculateJob($base);

        $this->assertSame('client_15_professional_15', $calculation->economicModelVersion);
        $this->assertSame($base, $calculation->baseAmount);
        $this->assertSame('15.00', $calculation->clientServiceFeePercent);
        $this->assertSame($fee, $calculation->clientServiceFee);
        $this->assertSame('15.00', $calculation->professionalCommissionPercent);
        $this->assertSame($fee, $calculation->professionalCommission);
        $this->assertSame($customerTotal, $calculation->customerTotal);
        $this->assertSame($professionalAmount, $calculation->professionalAmountBeforeExternalCosts);
        $this->assertSame($platformGross, $calculation->platformGrossFee);
    }

    public static function calculationProvider(): array
    {
        return [
            ['100.00', '15.00', '85.00'],
            ['500.00', '75.00', '425.00'],
            ['999.99', '150.00', '849.99'],
            ['333.33', '50.00', '283.33'],
            ['100.01', '15.00', '85.01'],
            ['1000.00', '150.00', '850.00'],
            ['2000.00', '300.00', '1700.00'],
            ['2500.00', '375.00', '2125.00'],
        ];
    }

    public static function dualFeeProvider(): array
    {
        return [
            ['1000.00', '150.00', '1150.00', '850.00', '300.00'],
            ['100.00', '15.00', '115.00', '85.00', '30.00'],
            ['99.99', '15.00', '114.99', '84.99', '30.00'],
            ['333.33', '50.00', '383.33', '283.33', '100.00'],
            ['1.00', '0.15', '1.15', '0.85', '0.30'],
            ['99999999.99', '15000000.00', '114999999.99', '84999999.99', '30000000.00'],
        ];
    }

    private function addMoney(string $left, string $right): string
    {
        [$leftWhole, $leftCents] = explode('.', $left);
        [$rightWhole, $rightCents] = explode('.', $right);
        $cents = ((int) $leftWhole * 100) + (int) $leftCents + ((int) $rightWhole * 100) + (int) $rightCents;

        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
