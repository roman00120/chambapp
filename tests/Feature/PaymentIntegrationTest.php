<?php

namespace Tests\Feature;

use App\Enums\JobStatus;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Models\JobQuote;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use App\Models\User;
use App\Services\MercadoPagoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_checkout_uses_server_amount_fee_and_reuses_active_payment(): void
    {
        [$job, $client] = $this->awaitingPaymentFixture();
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('createPreference')->once()->withArgs(function (Payment $payment): bool {
            return $payment->gross_amount === '1000.00'
                && $payment->platform_fee_percent === '15.00'
                && $payment->platform_fee === '150.00'
                && $payment->professional_amount === '850.00';
        })->andReturn(['id' => 'pref-1', 'url' => 'https://sandbox.mercadopago.test/checkout/1']);
        $this->app->instance(MercadoPagoService::class, $provider);

        $payload = ['amount' => '1', 'platform_fee' => '0', 'platform_fee_percent' => '0'];
        $this->actingAs($client)->post(route('client.payments.checkout', $job), $payload)->assertRedirect('https://sandbox.mercadopago.test/checkout/1');
        $this->actingAs($client)->post(route('client.payments.checkout', $job), $payload)->assertRedirect('https://sandbox.mercadopago.test/checkout/1');

        $payment = Payment::query()->firstOrFail();
        $this->assertSame('1000.00', $payment->gross_amount);
        $this->assertSame('15.00', $payment->platform_fee_percent);
        $this->assertSame('150.00', $payment->platform_fee);
        $this->assertSame('850.00', $payment->professional_amount);
        $this->assertSame('MXN', $payment->currency);
        $this->assertSame(1, Payment::query()->count());
        $this->assertSame(1, PaymentTransaction::query()->where('event_type', 'checkout.created')->count());
    }

    public function test_mercado_pago_preference_uses_seller_token_and_marketplace_fee(): void
    {
        [$job, , , $payment] = $this->awaitingPaymentFixture(true, true);
        Http::fake(['https://api.mercadopago.com/checkout/preferences' => Http::response([
            'id' => 'pref-real-shape',
            'init_point' => 'https://www.mercadopago.com/checkout/1',
            'sandbox_init_point' => 'https://sandbox.mercadopago.com/checkout/1',
        ])]);

        $result = (new MercadoPagoService)->createPreference($payment);

        $this->assertSame('pref-real-shape', $result['id']);
        Http::assertSent(function ($request): bool {
            return $request->hasHeader('Authorization', 'Bearer seller-token')
                && $request->data()['marketplace_fee'] === '150.00'
                && $request->data()['external_reference'] !== ''
                && $request->data()['items'][0]['unit_price'] === '1000.00';
        });
    }

    public function test_checkout_is_forbidden_for_non_owner_or_unconnected_professional(): void
    {
        [$job, $client] = $this->awaitingPaymentFixture(false);
        $otherClient = User::factory()->client()->create();
        $professional = User::factory()->professional()->create();

        $this->actingAs($otherClient)->post(route('client.payments.checkout', $job))->assertForbidden();
        $this->actingAs($professional)->post(route('client.payments.checkout', $job))->assertForbidden();
        $this->actingAs($client)->post(route('client.payments.checkout', $job))->assertForbidden();
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_professional_can_connect_with_oauth_state_and_tokens_are_hidden(): void
    {
        [$job, , $professional] = $this->awaitingPaymentFixture(false);
        config([
            'services.mercadopago.client_id' => 'app-id',
            'services.mercadopago.client_secret' => 'app-secret',
        ]);

        $this->actingAs($professional)->get(route('professional.payments.connect'))->assertRedirectContains('https://auth.mercadopago.com.mx/authorization');
        $state = session('mercadopago.oauth.state');
        $this->assertNotEmpty($state);
        Http::fake(['https://api.mercadopago.com/oauth/token' => Http::response([
            'access_token' => 'seller-access-token',
            'refresh_token' => 'seller-refresh-token',
            'public_key' => 'seller-public-key',
            'user_id' => 'seller-123',
            'expires_in' => 15552000,
        ])]);

        $this->actingAs($professional)->get(route('professional.payments.oauth-callback', ['code' => 'auth-code', 'state' => $state]))->assertRedirect(route('professional.payments.settings'));

        $profile = $professional->fresh()->professionalProfile;
        $this->assertSame('seller-123', $profile->mercadopago_user_id);
        $this->assertSame('seller-access-token', $profile->mercadopago_access_token);
        $this->assertArrayNotHasKey('mercadopago_access_token', $profile->toArray());
        $this->assertArrayNotHasKey('mercadopago_refresh_token', $profile->toArray());
        $this->assertTrue($profile->isMercadoPagoConnected());
    }

    public function test_valid_approved_webhook_verifies_provider_data_and_marks_job_paid(): void
    {
        [$job, $client, $professional, $payment] = $this->awaitingPaymentFixture(true, true);
        config(['services.mercadopago.webhook_secret' => 'webhook-secret']);
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPayment')->once()->andReturn($this->providerPayment($payment, 'approved', 'mp-1', '1000.00'));
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->actingAs($professional)->get(route('job-requests.show', $job))
            ->assertDontSee($client->phone)
            ->assertDontSee($job->address);

        $response = $this->postSignedWebhook('mp-1', 'event-1', $professional);

        $response->assertOk()->assertJson(['received' => true]);
        $this->assertSame(PaymentStatus::APPROVED, $payment->fresh()->status);
        $this->assertSame(JobStatus::PAID, $job->fresh()->status);
        $this->assertSame('20.00', $payment->fresh()->provider_fee);
        $this->assertSame(1, PaymentTransaction::query()->where('provider_event_id', 'event-1')->count());
        $this->assertCount(1, $client->fresh()->notifications);
        $this->assertCount(1, $professional->fresh()->notifications);
        $this->actingAs($professional)->get(route('job-requests.show', $job))
            ->assertSee($client->phone)
            ->assertSee($job->address);
        $this->actingAs($professional)->post(route('job-requests.start', $job))->assertRedirect();
        $this->assertSame(JobStatus::IN_PROGRESS, $job->fresh()->status);
    }

    public function test_duplicate_webhook_is_idempotent_and_does_not_duplicate_notifications(): void
    {
        [$job, $client, $professional, $payment] = $this->awaitingPaymentFixture(true, true);
        config(['services.mercadopago.webhook_secret' => 'webhook-secret']);
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPayment')->twice()->andReturn($this->providerPayment($payment, 'approved', 'mp-2', '1000.00'));
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->postSignedWebhook('mp-2', 'event-2', $professional)->assertOk();
        $this->postSignedWebhook('mp-2', 'event-2', $professional)->assertOk();

        $this->assertSame(1, PaymentTransaction::query()->where('provider_event_id', 'event-2')->count());
        $this->assertCount(1, $client->fresh()->notifications);
        $this->assertCount(1, $professional->fresh()->notifications);
        $this->assertSame(JobStatus::PAID, $job->fresh()->status);
    }

    public function test_invalid_signature_and_amount_mismatch_have_no_financial_effect(): void
    {
        [$job, , $professional, $payment] = $this->awaitingPaymentFixture(true, true);
        config(['services.mercadopago.webhook_secret' => 'webhook-secret']);
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPayment')->once()->andReturn($this->providerPayment($payment, 'approved', 'mp-3', '1.00'));
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->postJson(route('webhooks.mercadopago').'?data.id=mp-3', [
            'id' => 'event-invalid',
            'type' => 'payment',
            'user_id' => $professional->professionalProfile->mercadopago_user_id,
            'data' => ['id' => 'mp-3'],
        ], ['x-signature' => 'ts=1,v1=bad', 'x-request-id' => 'bad'])->assertUnauthorized();

        $this->postSignedWebhook('mp-3', 'event-3', $professional)->assertOk();
        $this->assertSame(PaymentStatus::PENDING, $payment->fresh()->status);
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->fresh()->status);
        $this->assertDatabaseHas('payment_transactions', ['payment_id' => $payment->id, 'event_type' => 'webhook.rejected']);
    }

    public function test_rejected_provider_payment_keeps_job_awaiting_payment(): void
    {
        [$job, $client, $professional, $payment] = $this->awaitingPaymentFixture(true, true);
        config(['services.mercadopago.webhook_secret' => 'webhook-secret']);
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPayment')->once()->andReturn($this->providerPayment($payment, 'rejected', 'mp-4', '1000.00'));
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->postSignedWebhook('mp-4', 'event-4', $professional)->assertOk();

        $this->assertSame(PaymentStatus::REJECTED, $payment->fresh()->status);
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->fresh()->status);
        $this->assertCount(1, $client->fresh()->notifications);
    }

    public function test_return_url_does_not_approve_payment_by_itself(): void
    {
        [$job, $client, , $payment] = $this->awaitingPaymentFixture(true, true);

        $this->actingAs($client)->get(route('payments.return.success'))->assertOk();

        $this->assertSame(PaymentStatus::PENDING, $payment->fresh()->status);
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->fresh()->status);
    }

    private function awaitingPaymentFixture(bool $connected = true, bool $withPayment = false): array
    {
        $client = User::factory()->client()->create();
        $professional = User::factory()->professional()->create();
        $profileData = ['user_id' => $professional->id];
        if ($connected) {
            $profileData += [
                'mercadopago_user_id' => 'seller-'.$professional->id,
                'mercadopago_access_token' => 'seller-token',
                'mercadopago_refresh_token' => 'seller-refresh',
                'mercadopago_token_expires_at' => now()->addHour(),
            ];
        }
        $profile = ProfessionalProfile::factory()->create($profileData);
        $service = Service::factory()->create(['professional_id' => $profile->id]);
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $profile->id,
            'service_id' => $service->id,
            'agreed_price' => '1000.00',
            'status' => JobStatus::AWAITING_PAYMENT,
            'accepted_at' => now(),
        ]);
        JobQuote::factory()->create([
            'job_request_id' => $job->id,
            'professional_id' => $profile->id,
            'amount' => '1000.00',
            'status' => QuoteStatus::ACCEPTED,
            'accepted_at' => now(),
        ]);

        $payment = null;
        if ($withPayment) {
            $payment = Payment::factory()->create([
                'job_request_id' => $job->id,
                'client_id' => $client->id,
                'professional_id' => $profile->id,
                'external_reference' => sprintf('CHAMBAPP-JOB-%06d-PAY-000001', $job->id),
                'gross_amount' => '1000.00',
                'platform_fee_percent' => '15.00',
                'platform_fee' => '150.00',
                'provider_fee' => null,
                'professional_amount' => '850.00',
                'status' => PaymentStatus::PENDING,
            ]);
        }

        return [$job, $client, $professional, $payment];
    }

    private function providerPayment(Payment $payment, string $status, string $providerId, string $amount): array
    {
        return [
            'id' => $providerId,
            'status' => $status,
            'external_reference' => $payment->external_reference,
            'transaction_amount' => $amount,
            'currency_id' => 'MXN',
            'fee_details' => [['type' => 'mercadopago_fee', 'amount' => '20.00']],
        ];
    }

    private function postSignedWebhook(string $paymentId, string $eventId, User $professional)
    {
        $requestId = 'request-'.$eventId;
        $timestamp = (string) now()->timestamp;
        $manifest = 'id:'.$paymentId.';request-id:'.$requestId.';ts:'.$timestamp.';';
        $signature = hash_hmac('sha256', $manifest, 'webhook-secret');

        return $this->postJson(route('webhooks.mercadopago').'?data.id='.$paymentId, [
            'id' => $eventId,
            'type' => 'payment',
            'action' => 'payment.updated',
            'user_id' => $professional->professionalProfile->mercadopago_user_id,
            'data' => ['id' => $paymentId],
        ], [
            'x-signature' => 'ts='.$timestamp.',v1='.$signature,
            'x-request-id' => $requestId,
        ]);
    }
}
