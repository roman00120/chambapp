<?php

namespace Tests\Unit;

use App\Enums\PaymentStatus;
use App\Services\PaymentStatusMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PaymentStatusMapperTest extends TestCase
{
    #[DataProvider('statusProvider')]
    public function test_provider_statuses_are_mapped_centrally(string $provider, PaymentStatus $expected): void
    {
        $this->assertSame($expected, (new PaymentStatusMapper)->map($provider));
    }

    public static function statusProvider(): array
    {
        return [
            ['approved', PaymentStatus::APPROVED],
            ['rejected', PaymentStatus::REJECTED],
            ['in_process', PaymentStatus::PROCESSING],
            ['pending', PaymentStatus::PROCESSING],
            ['cancelled', PaymentStatus::CANCELLED],
            ['refunded', PaymentStatus::REFUNDED],
            ['partially_refunded', PaymentStatus::PARTIALLY_REFUNDED],
            ['charged_back', PaymentStatus::CHARGED_BACK],
            ['in_mediation', PaymentStatus::IN_MEDIATION],
        ];
    }
}
