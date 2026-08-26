<?php

namespace Tests\Unit;

use App\Exceptions\MercadoPagoException;
use App\Services\MercadoPagoService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MercadoPagoServiceResilienceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.mercadopago.api_url' => 'https://api.mercadopago.com',
            'services.mercadopago.access_token' => 'test-platform-token',
            'chambapp.payments.read_retry_attempts' => 3,
            'chambapp.payments.read_retry_base_milliseconds' => 0,
            'chambapp.payments.read_retry_max_milliseconds' => 0,
        ]);
    }

    #[DataProvider('transientStatusProvider')]
    public function test_safe_payment_reads_retry_transient_provider_errors(int $status): void
    {
        Http::fakeSequence()
            ->push(['message' => 'temporary'], $status)
            ->push(['id' => 'mp-123'], 200);

        $payment = (new MercadoPagoService)->getPlatformPayment('mp-123');

        $this->assertSame('mp-123', $payment['id']);
        Http::assertSentCount(2);
    }

    public function test_safe_read_retries_429_with_retry_after_then_succeeds(): void
    {
        Http::fakeSequence()
            ->push(['message' => 'slow down'], 429, ['Retry-After' => '0'])
            ->push(['id' => 'mp-429'], 200);

        $payment = (new MercadoPagoService)->getPlatformPayment('mp-429');

        $this->assertSame('mp-429', $payment['id']);
        Http::assertSentCount(2);
    }

    public function test_safe_read_retries_network_failures_then_raises_a_sanitized_error(): void
    {
        $attempts = 0;
        Http::fake(function () use (&$attempts): never {
            $attempts++;

            throw new ConnectionException('DNS failure');
        });

        $this->expectException(MercadoPagoException::class);
        $this->expectExceptionMessage('consulta segura');

        try {
            (new MercadoPagoService)->getPlatformPayment('mp-network');
        } finally {
            $this->assertSame(3, $attempts);
        }
    }

    public function test_safe_read_does_not_retry_malformed_success_response(): void
    {
        Http::fake(['https://api.mercadopago.com/v1/payments/*' => Http::response('not-json', 200)]);

        $this->expectException(MercadoPagoException::class);

        try {
            (new MercadoPagoService)->getPlatformPayment('mp-malformed');
        } finally {
            Http::assertSentCount(1);
        }
    }

    public static function transientStatusProvider(): array
    {
        return [[500], [502], [503], [504]];
    }
}
