<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Exceptions\MercadoPagoException;
use App\Models\CommerceOrder;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use App\Services\CommerceService;
use App\Services\MercadoPagoService;
use App\Services\PaymentCalculationService;
use App\Services\PaymentStatusMapper;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Mockery;
use Tests\TestCase;

class CommercePaymentHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_identical_pending_order_is_reused(): void
    {
        [$professional, $service] = $this->professionalAndService();
        $commerce = $this->commerceWith(Mockery::mock(MercadoPagoService::class));

        $first = $commerce->createFeaturedOrder($professional, $service, 7);
        $same = $commerce->createFeaturedOrder($professional, $service, 7);
        $different = $commerce->createFeaturedOrder($professional, $service, 1);

        $this->assertSame($first->getKey(), $same->getKey());
        $this->assertNotSame($first->getKey(), $different->getKey());
        $this->assertDatabaseCount('commerce_orders', 2);
    }

    public function test_checkout_reuses_the_saved_provider_preference(): void
    {
        [$professional, $service] = $this->professionalAndService();
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('createPlatformPreference')
            ->once()
            ->withArgs(fn (string $title, string $amount, string $reference): bool => $title === 'featured-7'
                && $amount === '199.00'
                && $reference !== '')
            ->andReturn(['id' => 'pref-commerce-1', 'url' => 'https://sandbox.mercadopago.test/commerce/1']);
        $commerce = $this->commerceWith($provider);
        $order = $commerce->createFeaturedOrder($professional, $service, 7);

        $first = $commerce->checkout($order);
        $second = $commerce->checkout($order);

        $this->assertSame('pref-commerce-1', $first->external_preference_id);
        $this->assertSame($first->checkout_url, $second->checkout_url);
        $this->assertDatabaseCount('commerce_orders', 1);
    }

    public function test_failed_checkout_can_resume_the_same_order(): void
    {
        [$professional, $service] = $this->professionalAndService();
        $attempts = 0;
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('createPlatformPreference')
            ->twice()
            ->andReturnUsing(function () use (&$attempts): array {
                $attempts++;
                if ($attempts === 1) {
                    throw new MercadoPagoException('Temporalmente no disponible.');
                }

                return ['id' => 'pref-retry', 'url' => 'https://sandbox.mercadopago.test/commerce/retry'];
            });
        $commerce = $this->commerceWith($provider);
        $order = $commerce->createFeaturedOrder($professional, $service, 7);

        try {
            $commerce->checkout($order);
            $this->fail('The first checkout should fail.');
        } catch (MercadoPagoException) {
            $this->assertNull($order->fresh()->checkout_url);
        }

        $same = $commerce->createFeaturedOrder($professional, $service, 7);
        $resumed = $commerce->checkout($same);

        $this->assertSame($order->getKey(), $same->getKey());
        $this->assertSame('pref-retry', $resumed->external_preference_id);
        $this->assertDatabaseCount('commerce_orders', 1);
    }

    public function test_paid_featured_orders_accumulate_days_and_each_order_applies_once(): void
    {
        $this->travelTo(Carbon::parse('2026-08-21 12:00:00'));
        [$professional, $service] = $this->professionalAndService();
        $commerce = $this->commerceWith(Mockery::mock(MercadoPagoService::class));
        $sevenDays = $commerce->createFeaturedOrder($professional, $service, 7);
        $oneDay = $commerce->createFeaturedOrder($professional, $service, 1);

        $commerce->applyPaidOrder($sevenDays);
        $commerce->applyPaidOrder($oneDay);
        $commerce->applyPaidOrder($sevenDays);

        $service->refresh();
        $this->assertTrue($service->is_featured);
        $this->assertSame('2026-08-29 12:00:00', $service->featured_until?->format('Y-m-d H:i:s'));
        $this->assertSame('approved', $sevenDays->fresh()->status);
        $this->assertSame('approved', $oneDay->fresh()->status);
    }

    public function test_customization_is_applied_atomically_and_only_to_the_order_owner(): void
    {
        [$professional] = $this->professionalAndService();
        $commerce = $this->commerceWith(Mockery::mock(MercadoPagoService::class));
        $order = $commerce->createCustomizationOrder($professional, 'theme-sunset');

        $commerce->applyPaidOrder($order);
        $commerce->applyPaidOrder($order);

        $this->assertSame('sunset', $professional->fresh()->profile_theme);
        $this->assertSame('approved', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_inactive_service_and_malformed_store_item_cannot_be_charged(): void
    {
        [$professional, $service] = $this->professionalAndService();
        $commerce = $this->commerceWith(Mockery::mock(MercadoPagoService::class));
        $service->forceFill(['is_active' => false])->save();

        try {
            $commerce->createFeaturedOrder($professional, $service, 7);
            $this->fail('An inactive service should not create an order.');
        } catch (DomainException) {
            $this->assertDatabaseCount('commerce_orders', 0);
        }

        config(['chambapp.commerce.store_items.broken' => [
            'kind' => 'unsupported',
            'name' => 'Broken',
            'price' => '49.00',
            'value' => 'broken',
        ]]);

        $this->expectException(DomainException::class);
        $commerce->createCustomizationOrder($professional, 'broken');
    }

    public function test_valid_platform_webhook_fulfills_order_only_with_matching_financial_data(): void
    {
        [$professional, $service] = $this->professionalAndService();
        config([
            'services.mercadopago.webhook_secret' => 'webhook-secret',
            'services.mercadopago.user_id' => 'platform-collector',
        ]);
        $order = $this->commerceWith(Mockery::mock(MercadoPagoService::class))
            ->createFeaturedOrder($professional, $service, 7);
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPlatformPayment')->twice()->andReturn($this->platformPayment($order, 'approved'));
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->postSignedPlatformWebhook('mp-commerce-valid', 'commerce-valid')->assertOk();
        $this->postSignedPlatformWebhook('mp-commerce-valid', 'commerce-valid')->assertOk();

        $order->refresh();
        $this->assertSame('approved', $order->status);
        $this->assertSame(PaymentStatus::APPROVED, $order->financial_status);
        $this->assertSame('mp-commerce-valid', $order->external_payment_id);
        $this->assertSame(1, $order->events()->where('event_type', 'webhook.received')->count());
        $this->assertSame(1, $order->events()->where('event_type', 'fulfillment.completed')->count());
        $this->assertTrue($service->fresh()->is_featured);
    }

    public function test_platform_webhook_moves_refunded_order_to_review_without_reapplying_fulfillment(): void
    {
        [$professional, $service] = $this->professionalAndService();
        config([
            'services.mercadopago.webhook_secret' => 'webhook-secret',
            'services.mercadopago.user_id' => 'platform-collector',
        ]);
        $order = $this->commerceWith(Mockery::mock(MercadoPagoService::class))
            ->createFeaturedOrder($professional, $service, 7);
        $approved = $this->platformPayment($order, 'approved');
        $refunded = $this->platformPayment($order, 'refunded');
        $refunded['transaction_amount_refunded'] = (string) $order->amount;
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPlatformPayment')->twice()->andReturn($approved, $refunded);
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->postSignedPlatformWebhook('mp-commerce-valid', 'commerce-approved')->assertOk();
        $this->postSignedPlatformWebhook('mp-commerce-valid', 'commerce-refunded')->assertOk();

        $order->refresh();
        $this->assertSame('review', $order->status);
        $this->assertSame(PaymentStatus::REFUNDED, $order->financial_status);
        $this->assertSame((string) $order->amount, (string) $order->refunded_amount);
        $this->assertSame(1, $order->events()->where('event_type', 'fulfillment.completed')->count());
        $this->assertSame(1, $order->events()->where('event_type', 'payment.requires_review')->count());
    }

    public function test_platform_webhook_with_wrong_collector_is_acknowledged_but_not_fulfilled(): void
    {
        [$professional, $service] = $this->professionalAndService();
        config([
            'services.mercadopago.webhook_secret' => 'webhook-secret',
            'services.mercadopago.user_id' => 'platform-collector',
        ]);
        $order = $this->commerceWith(Mockery::mock(MercadoPagoService::class))
            ->createFeaturedOrder($professional, $service, 7);
        $providerData = $this->platformPayment($order, 'approved');
        $providerData['collector_id'] = 'another-collector';
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPlatformPayment')->once()->andReturn($providerData);
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->postSignedPlatformWebhook('mp-commerce-valid', 'commerce-wrong-collector')->assertOk();

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertFalse($service->fresh()->is_featured);
    }

    public function test_real_create_platform_preference_invokes_post_once_with_platform_preference(): void
    {
        config([
            'services.mercadopago.access_token' => 'TEST-PLATFORM-TOKEN',
            'services.mercadopago.user_id' => '123456789',
        ]);

        Http::fake([
            'https://api.mercadopago.com/checkout/preferences' => Http::response([
                'id' => 'pref-platform-test-123',
                'init_point' => 'https://www.mercadopago.com.mx/checkout/v1/redirect?pref_id=pref-platform-test-123',
                'sandbox_init_point' => 'https://sandbox.mercadopago.com.mx/checkout/v1/redirect?pref_id=pref-platform-test-123',
            ], 201),
        ]);

        $service = app(MercadoPagoService::class);
        $result = $service->createPlatformPreference('featured-7', '199.00', 'CHAMBAPP-COM-1-ABC');

        $this->assertSame('pref-platform-test-123', $result['id']);
        $this->assertNotEmpty($result['url']);

        Http::assertSent(function (\Illuminate\Http\Client\Request $request): bool {
            return $request->url() === 'https://api.mercadopago.com/checkout/preferences'
                && $request['external_reference'] === 'CHAMBAPP-COM-1-ABC'
                && $request['items'][0]['unit_price'] === 199.0;
        });
    }

    public function test_buy_featured_route_dispatches_platform_preference_and_redirects(): void
    {
        [$professional, $service] = $this->professionalAndService();

        config([
            'services.mercadopago.access_token' => 'TEST-PLATFORM-TOKEN',
            'services.mercadopago.user_id' => '123456789',
        ]);

        Http::fake([
            'https://api.mercadopago.com/checkout/preferences' => Http::response([
                'id' => 'pref-platform-featured-999',
                'init_point' => 'https://www.mercadopago.com.mx/checkout/v1/redirect?pref_id=pref-platform-featured-999',
                'sandbox_init_point' => 'https://sandbox.mercadopago.com.mx/checkout/v1/redirect?pref_id=pref-platform-featured-999',
            ], 201),
        ]);

        $response = $this->actingAs($professional->user)
            ->post(route('professional.commerce.featured.buy', $service), [
                'days' => 7,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('commerce_orders', [
            'professional_id' => $professional->getKey(),
            'service_id' => $service->getKey(),
            'kind' => 'featured',
            'external_preference_id' => 'pref-platform-featured-999',
        ]);
    }

    private function professionalAndService(): array
    {
        $professional = ProfessionalProfile::factory()->verifiedIdentity()->create();
        $service = Service::factory()->create(['professional_id' => $professional->getKey()]);

        return [$professional, $service];
    }

    private function commerceWith(MercadoPagoService $provider): CommerceService
    {
        return new CommerceService(
            $provider,
            app(PaymentCalculationService::class),
            app(PaymentStatusMapper::class),
        );
    }

    private function platformPayment(CommerceOrder $order, string $status): array
    {
        return [
            'id' => 'mp-commerce-valid',
            'status' => $status,
            'external_reference' => $order->external_reference,
            'transaction_amount' => (string) $order->amount,
            'currency_id' => $order->currency,
            'collector_id' => 'platform-collector',
            'live_mode' => app()->environment('production'),
        ];
    }

    private function postSignedPlatformWebhook(string $paymentId, string $eventId): TestResponse
    {
        $requestId = 'request-'.$eventId;
        $timestamp = (string) now()->timestamp;
        $manifest = 'id:'.$paymentId.';request-id:'.$requestId.';ts:'.$timestamp.';';
        $signature = hash_hmac('sha256', $manifest, 'webhook-secret');

        return $this->postJson(route('webhooks.mercadopago').'?data.id='.$paymentId, [
            'id' => $eventId,
            'type' => 'payment',
            'action' => 'payment.updated',
            'user_id' => 'platform-collector',
            'data' => ['id' => $paymentId],
        ], [
            'x-signature' => 'ts='.$timestamp.',v1='.$signature,
            'x-request-id' => $requestId,
        ]);
    }
}
