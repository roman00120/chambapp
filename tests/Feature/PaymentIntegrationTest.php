<?php

namespace Tests\Feature;

use App\Enums\JobStatus;
use App\Enums\PaymentKind;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Models\JobQuote;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use App\Models\User;
use App\Exceptions\MercadoPagoException;
use App\Services\MercadoPagoService;
use App\Services\PaymentService;
use App\Services\JobWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mercadopago_webhook_reaches_signature_validation_without_csrf_token(): void
    {
        config(['services.mercadopago.webhook_secret' => 'test-webhook-secret']);

        $this->postJson('/webhooks/mercadopago', [
            'type' => 'payment',
            'data' => ['id' => 'invalid-signature-payment'],
        ], [
            'x-signature' => 'ts=0,v1=invalid',
            'x-request-id' => 'm8-csrf-regression',
        ])->assertUnauthorized();
    }

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

    public function test_new_quote_freezes_dual_fee_snapshot_and_checkout_uses_customer_total(): void
    {
        $client = User::factory()->client()->create();
        $professional = User::factory()->professional()->create();
        $profile = ProfessionalProfile::factory()->verifiedIdentity()->create([
            'user_id' => $professional->id,
            'mercadopago_user_id' => 'seller-dual',
            'mercadopago_access_token' => 'seller-token',
            'mercadopago_refresh_token' => 'seller-refresh',
            'mercadopago_token_expires_at' => now()->addMonths(5),
        ]);
        $service = Service::factory()->create(['professional_id' => $profile->id]);
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $profile->id,
            'service_id' => $service->id,
            'agreed_price' => null,
            'status' => JobStatus::ACCEPTED,
        ]);
        $quote = JobQuote::factory()->create([
            'job_request_id' => $job->id,
            'professional_id' => $profile->id,
            'amount' => '1000.00',
            'status' => QuoteStatus::PENDING,
        ]);

        app(JobWorkflowService::class)->acceptQuote($quote, $client);
        $job->refresh();
        $this->assertSame('client_15_professional_15', $job->economic_model_version);
        $this->assertSame('1000.00', $job->base_amount);
        $this->assertSame('150.00', $job->client_service_fee);
        $this->assertSame('150.00', $job->professional_commission);
        $this->assertSame('1150.00', $job->customer_total);
        $this->assertSame('300.00', $job->platform_gross_fee);
        $this->assertSame('850.00', $job->professional_amount_before_external_costs);

        config([
            'chambapp.payments.client_service_fee_percent' => '20',
            'chambapp.payments.professional_commission_percent' => '20',
        ]);

        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('createPreference')->once()->withArgs(function (Payment $payment): bool {
            return $payment->economic_model_version === 'client_15_professional_15'
                && $payment->gross_amount === '1150.00'
                && $payment->platform_fee === '300.00'
                && $payment->professional_amount === '850.00'
                && $payment->customer_total === '1150.00'
                && $payment->platform_gross_fee === '300.00';
        })->andReturn(['id' => 'pref-dual', 'url' => 'https://sandbox.mercadopago.test/checkout/dual']);
        $this->app->instance(MercadoPagoService::class, $provider);

        app(PaymentService::class)->startCheckout($job, $client);
        $this->assertDatabaseHas('payments', [
            'job_request_id' => $job->id,
            'base_amount' => '1000.00',
            'client_service_fee' => '150.00',
            'professional_commission' => '150.00',
            'customer_total' => '1150.00',
            'platform_gross_fee' => '300.00',
            'professional_amount_before_external_costs' => '850.00',
        ]);
    }

    public function test_dual_fee_mercado_pago_preference_uses_1150_transaction_and_300_marketplace_fee(): void
    {
        [$job, $client] = $this->awaitingPaymentFixture();
        $money = app(\App\Services\PaymentCalculationService::class)->calculateJob('1000.00');
        $job->forceFill([
            'economic_model_version' => $money->economicModelVersion,
            'base_amount' => $money->baseAmount,
            'client_service_fee_percent' => $money->clientServiceFeePercent,
            'client_service_fee' => $money->clientServiceFee,
            'professional_commission_percent' => $money->professionalCommissionPercent,
            'professional_commission' => $money->professionalCommission,
            'customer_total' => $money->customerTotal,
            'platform_gross_fee' => $money->platformGrossFee,
            'professional_amount_before_external_costs' => $money->professionalAmountBeforeExternalCosts,
        ])->save();
        Http::fake(['https://api.mercadopago.com/checkout/preferences' => Http::response([
            'id' => 'pref-dual-shape',
            'init_point' => 'https://www.mercadopago.com/checkout/dual',
            'sandbox_init_point' => 'https://sandbox.mercadopago.com/checkout/dual',
        ])]);

        $payment = app(PaymentService::class)->startCheckout($job->fresh(), $client);

        Http::assertSent(fn ($request): bool => $request->data()['items'][0]['unit_price'] === 1150.0
            && $request->data()['marketplace_fee'] === 300.0);
        $this->assertSame('1150.00', $payment->gross_amount);
        $this->assertSame('300.00', $payment->platform_fee);
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
                && $request->data()['marketplace_fee'] === 150.0
                && $request->data()['external_reference'] !== ''
                && $request->data()['items'][0]['unit_price'] === 1000.0
                && $request->data()['expires'] === true
                && filled($request->data()['expiration_date_to']);
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

    public function test_expired_mercado_pago_oauth_state_is_rejected_without_exchanging_the_code(): void
    {
        [, , $professional] = $this->awaitingPaymentFixture(false);
        config([
            'services.mercadopago.client_id' => 'app-id',
            'services.mercadopago.client_secret' => 'app-secret',
            'chambapp.payments.oauth_state_lifetime_seconds' => 60,
        ]);
        Http::fake();

        $this->actingAs($professional)->get(route('professional.payments.connect'))->assertRedirect();
        $state = session('mercadopago.oauth.state');
        $this->travel(61)->seconds();

        $this->actingAs($professional)
            ->get(route('professional.payments.oauth-callback', ['code' => 'expired-code', 'state' => $state]))
            ->assertRedirect(route('professional.payments.settings'))
            ->assertSessionHasErrors('payment');

        Http::assertNothingSent();
        $this->assertNull($professional->fresh()->professionalProfile->mercadopago_user_id);
    }

    public function test_same_mercado_pago_seller_cannot_be_linked_to_two_professionals(): void
    {
        [, , $professional] = $this->awaitingPaymentFixture(false);
        $otherProfessional = User::factory()->professional()->create();
        ProfessionalProfile::factory()->create([
            'user_id' => $otherProfessional->id,
            'mercadopago_user_id' => 'seller-shared',
            'mercadopago_access_token' => 'existing-token',
        ]);
        config([
            'services.mercadopago.client_id' => 'app-id',
            'services.mercadopago.client_secret' => 'app-secret',
        ]);
        $this->actingAs($professional)->get(route('professional.payments.connect'))->assertRedirect();
        $state = session('mercadopago.oauth.state');
        Http::fake(['https://api.mercadopago.com/oauth/token' => Http::response([
            'access_token' => 'new-token',
            'refresh_token' => 'new-refresh',
            'user_id' => 'seller-shared',
            'expires_in' => 15552000,
        ])]);

        $this->actingAs($professional)
            ->get(route('professional.payments.oauth-callback', ['code' => 'auth-code', 'state' => $state]))
            ->assertRedirect(route('professional.payments.settings'))
            ->assertSessionHasErrors('payment');

        $this->assertNull($professional->fresh()->professionalProfile->mercadopago_user_id);
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
        $this->assertSame('830.00', $payment->fresh()->professional_amount);
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

    public function test_stale_valid_signature_and_provider_id_mismatch_have_no_effect(): void
    {
        [$job, , $professional, $payment] = $this->awaitingPaymentFixture(true, true);
        config(['services.mercadopago.webhook_secret' => 'webhook-secret']);
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPayment')->once()->andReturn(
            $this->providerPayment($payment, 'approved', 'mp-different', '1000.00'),
        );
        $this->app->instance(MercadoPagoService::class, $provider);

        $requestId = 'request-stale';
        $timestamp = (string) now()->subMinutes(10)->timestamp;
        $manifest = 'id:mp-stale;request-id:'.$requestId.';ts:'.$timestamp.';';
        $signature = hash_hmac('sha256', $manifest, 'webhook-secret');
        $this->postJson(route('webhooks.mercadopago').'?data.id=mp-stale', [
            'id' => 'event-stale',
            'type' => 'payment',
            'user_id' => $professional->professionalProfile->mercadopago_user_id,
            'data' => ['id' => 'mp-stale'],
        ], [
            'x-signature' => 'ts='.$timestamp.',v1='.$signature,
            'x-request-id' => $requestId,
        ])->assertUnauthorized();

        $this->postSignedWebhook('mp-notified', 'event-id-mismatch', $professional)->assertOk();

        $this->assertSame(PaymentStatus::PENDING, $payment->fresh()->status);
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->fresh()->status);
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

    public function test_provider_outage_returns_retryable_status_and_does_not_change_payment(): void
    {
        [$job, , $professional, $payment] = $this->awaitingPaymentFixture(true, true);
        config(['services.mercadopago.webhook_secret' => 'webhook-secret']);
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPayment')->once()->andThrow(new MercadoPagoException('Temporary outage.'));
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->postSignedWebhook('mp-outage', 'event-outage', $professional)->assertStatus(503);

        $this->assertSame(PaymentStatus::PENDING, $payment->fresh()->status);
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->fresh()->status);
    }

    public function test_late_pending_webhook_cannot_regress_an_approved_payment(): void
    {
        [$job, $client, $professional, $payment] = $this->awaitingPaymentFixture(true, true);
        config(['services.mercadopago.webhook_secret' => 'webhook-secret']);
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPayment')->twice()->andReturn(
            $this->providerPayment($payment, 'approved', 'mp-monotonic', '1000.00'),
            $this->providerPayment($payment, 'pending', 'mp-monotonic', '1000.00'),
        );
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->postSignedWebhook('mp-monotonic', 'event-approved', $professional)->assertOk();
        $this->postSignedWebhook('mp-monotonic', 'event-late-pending', $professional)->assertOk();

        $this->assertSame(PaymentStatus::APPROVED, $payment->fresh()->status);
        $this->assertSame(JobStatus::PAID, $job->fresh()->status);
        $this->assertCount(1, $client->fresh()->notifications);
        $this->assertDatabaseHas('payment_transactions', [
            'payment_id' => $payment->id,
            'event_type' => 'webhook.ignored',
        ]);
    }

    public function test_second_provider_approval_for_same_checkout_freezes_job_as_duplicate_charge(): void
    {
        [$job, , $professional, $payment] = $this->awaitingPaymentFixture(true, true);
        config(['services.mercadopago.webhook_secret' => 'webhook-secret']);
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPayment')->twice()->andReturn(
            $this->providerPayment($payment, 'approved', 'mp-first-charge', '1000.00'),
            $this->providerPayment($payment, 'approved', 'mp-second-charge', '1000.00'),
        );
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->postSignedWebhook('mp-first-charge', 'event-first-charge', $professional)->assertOk();
        $this->postSignedWebhook('mp-second-charge', 'event-second-charge', $professional)->assertOk();

        $this->assertSame('mp-first-charge', $payment->fresh()->external_payment_id);
        $this->assertSame(JobStatus::DISPUTED, $job->fresh()->status);
        $this->assertDatabaseHas('payment_transactions', [
            'payment_id' => $payment->id,
            'event_type' => 'payment.duplicate_approved_detected',
        ]);
    }

    public function test_refunded_amount_overrides_approved_status_and_freezes_the_job(): void
    {
        [$job, , $professional, $payment] = $this->awaitingPaymentFixture(true, true);
        config(['services.mercadopago.webhook_secret' => 'webhook-secret']);
        $providerData = $this->providerPayment($payment, 'approved', 'mp-partial-refund', '1000.00');
        $providerData['transaction_amount_refunded'] = '250.00';
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPayment')->once()->andReturn($providerData);
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->postSignedWebhook('mp-partial-refund', 'event-partial-refund', $professional)->assertOk();

        $this->assertSame(PaymentStatus::PARTIALLY_REFUNDED, $payment->fresh()->status);
        $this->assertSame('250.00', $payment->fresh()->refunded_amount);
        $this->assertNotNull($payment->fresh()->paid_at);
        $this->assertSame(JobStatus::DISPUTED, $job->fresh()->status);
    }

    public function test_chargeback_with_zero_refund_amount_uses_gross_and_reopens_completed_job(): void
    {
        [$job, , $professional, $payment] = $this->awaitingPaymentFixture(true, true);
        config(['services.mercadopago.webhook_secret' => 'webhook-secret']);
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPayment')->twice()->andReturn(
            $this->providerPayment($payment, 'approved', 'mp-chargeback', '1000.00'),
            $this->providerPayment($payment, 'charged_back', 'mp-chargeback', '1000.00'),
        );
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->postSignedWebhook('mp-chargeback', 'event-before-chargeback', $professional)->assertOk();
        $job->forceFill(['status' => JobStatus::COMPLETED])->save();
        $this->postSignedWebhook('mp-chargeback', 'event-chargeback', $professional)->assertOk();

        $this->assertSame(PaymentStatus::CHARGED_BACK, $payment->fresh()->status);
        $this->assertSame('1000.00', $payment->fresh()->refunded_amount);
        $this->assertSame(JobStatus::DISPUTED, $job->fresh()->status);
    }

    public function test_mediation_after_approval_freezes_the_job(): void
    {
        [$job, , $professional, $payment] = $this->awaitingPaymentFixture(true, true);
        config(['services.mercadopago.webhook_secret' => 'webhook-secret']);
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPayment')->twice()->andReturn(
            $this->providerPayment($payment, 'approved', 'mp-mediation', '1000.00'),
            $this->providerPayment($payment, 'in_mediation', 'mp-mediation', '1000.00'),
        );
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->postSignedWebhook('mp-mediation', 'event-before-mediation', $professional)->assertOk();
        $this->postSignedWebhook('mp-mediation', 'event-mediation', $professional)->assertOk();

        $this->assertSame(PaymentStatus::IN_MEDIATION, $payment->fresh()->status);
        $this->assertSame(JobStatus::DISPUTED, $job->fresh()->status);
    }

    public function test_inconsistent_split_is_audited_and_does_not_activate_the_job(): void
    {
        [$job, , $professional, $payment] = $this->awaitingPaymentFixture(true, true);
        config(['services.mercadopago.webhook_secret' => 'webhook-secret']);
        $providerData = $this->providerPayment($payment, 'approved', 'mp-bad-split', '1000.00');
        $providerData['transaction_details']['net_received_amount'] = '950.00';
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPayment')->once()->andReturn($providerData);
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->postSignedWebhook('mp-bad-split', 'event-bad-split', $professional)->assertOk();

        $this->assertSame(PaymentStatus::APPROVED, $payment->fresh()->status);
        $this->assertSame('850.00', $payment->fresh()->professional_amount);
        $this->assertSame(JobStatus::DISPUTED, $job->fresh()->status);
        $this->assertDatabaseHas('payment_transactions', [
            'payment_id' => $payment->id,
            'event_type' => 'payment.split_mismatch',
        ]);
    }

    public function test_malformed_auxiliary_amounts_are_audited_without_losing_an_approval(): void
    {
        [$job, , $professional, $payment] = $this->awaitingPaymentFixture(true, true);
        config(['services.mercadopago.webhook_secret' => 'webhook-secret']);
        $providerData = $this->providerPayment($payment, 'approved', 'mp-malformed-aux', '1000.00');
        $providerData['fee_details'][0]['amount'] = 'not-money';
        $providerData['transaction_details']['net_received_amount'] = '-1';
        $providerData['transaction_amount_refunded'] = 'invalid';
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('getPayment')->once()->andReturn($providerData);
        $this->app->instance(MercadoPagoService::class, $provider);

        $this->postSignedWebhook('mp-malformed-aux', 'event-malformed-aux', $professional)->assertOk();

        $this->assertSame(PaymentStatus::APPROVED, $payment->fresh()->status);
        $this->assertSame(JobStatus::PAID, $job->fresh()->status);
        $this->assertDatabaseHas('payment_transactions', [
            'payment_id' => $payment->id,
            'event_type' => 'provider.data_ignored',
        ]);
    }

    public function test_reconciliation_selects_one_canonical_non_financial_attempt(): void
    {
        [$job, , , $payment] = $this->awaitingPaymentFixture(true, true);
        $processing = $this->providerPayment($payment, 'pending', 'mp-processing', '1000.00');
        $processing['date_last_updated'] = now()->subMinute()->toIso8601String();
        $rejected = $this->providerPayment($payment, 'rejected', 'mp-rejected', '1000.00');
        $rejected['date_last_updated'] = now()->toIso8601String();
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('searchPayments')->once()->andReturn([$rejected, $processing]);
        $this->app->instance(MercadoPagoService::class, $provider);

        app(PaymentService::class)->reconcile($payment);

        $this->assertSame(PaymentStatus::PROCESSING, $payment->fresh()->status);
        $this->assertSame('mp-processing', $payment->fresh()->external_payment_id);
        $this->assertNotNull($payment->fresh()->last_reconciled_at);
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->fresh()->status);
    }

    public function test_expired_checkout_is_renewed_and_tip_does_not_replace_job_payment(): void
    {
        [$job, $client, , $payment] = $this->awaitingPaymentFixture(true, true);
        $payment->forceFill([
            'status' => PaymentStatus::REJECTED,
            'checkout_url' => 'https://sandbox.mercadopago.test/expired',
            'external_preference_id' => 'pref-expired',
            'checkout_expires_at' => now()->subMinute(),
        ])->save();
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('createPreference')->once()->andReturn([
            'id' => 'pref-renewed',
            'url' => 'https://sandbox.mercadopago.test/renewed',
            'expires_at' => now()->addDay(),
        ]);
        $this->app->instance(MercadoPagoService::class, $provider);

        $renewed = app(PaymentService::class)->startCheckout($job, $client);

        $this->assertSame($payment->getKey(), $renewed->getKey());
        $this->assertSame('pref-renewed', $renewed->external_preference_id);
        $this->assertSame(PaymentStatus::PENDING, $renewed->status);
        $this->assertDatabaseHas('payment_transactions', ['payment_id' => $payment->id, 'event_type' => 'checkout.renewed']);

        $renewed->forceFill(['status' => PaymentStatus::APPROVED])->save();
        $job->forceFill(['status' => JobStatus::COMPLETED])->save();
        $tipProvider = Mockery::mock(MercadoPagoService::class);
        $tipProvider->shouldReceive('createPreference')->once()->andReturn([
            'id' => 'pref-tip',
            'url' => 'https://sandbox.mercadopago.test/tip',
            'expires_at' => now()->addDay(),
        ]);
        $this->app->instance(MercadoPagoService::class, $tipProvider);
        $firstTip = app(PaymentService::class)->startTipCheckout($job, $client, '100.00');
        $sameTip = app(PaymentService::class)->startTipCheckout($job, $client, '100.00');

        $this->assertSame(PaymentKind::TIP, $firstTip->kind);
        $this->assertSame($firstTip->getKey(), $sameTip->getKey());
        $jobPayment = $job->fresh()->payment;
        $this->assertNotNull($jobPayment);
        $this->assertSame($renewed->getKey(), $jobPayment->getKey());
        $this->assertSame(1, $job->fresh()->tips()->count());
    }

    public function test_legacy_checkout_without_expiration_is_renewed(): void
    {
        [$job, $client, , $payment] = $this->awaitingPaymentFixture(true, true);
        $payment->forceFill([
            'status' => PaymentStatus::REJECTED,
            'checkout_url' => 'https://sandbox.mercadopago.test/legacy',
            'external_preference_id' => 'pref-legacy',
            'checkout_expires_at' => null,
        ])->save();
        $provider = Mockery::mock(MercadoPagoService::class);
        $provider->shouldReceive('createPreference')->once()->andReturn([
            'id' => 'pref-legacy-renewed',
            'url' => 'https://sandbox.mercadopago.test/legacy-renewed',
            'expires_at' => now()->addDay(),
        ]);
        $this->app->instance(MercadoPagoService::class, $provider);

        $renewed = app(PaymentService::class)->startCheckout($job, $client);

        $this->assertSame($payment->getKey(), $renewed->getKey());
        $this->assertSame('pref-legacy-renewed', $renewed->external_preference_id);
        $this->assertNotNull($renewed->checkout_expires_at);
        $this->assertDatabaseHas('payment_transactions', [
            'payment_id' => $payment->id,
            'event_type' => 'checkout.renewed',
        ]);
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
                'mercadopago_token_expires_at' => now()->addMonths(5),
            ];
        }
        $profile = ProfessionalProfile::factory()->verifiedIdentity()->create($profileData);
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
                'provider' => 'mercadopago',
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
            'collector_id' => $payment->professional->mercadopago_user_id,
            'live_mode' => app()->environment('production'),
            'fee_details' => [['type' => 'mercadopago_fee', 'amount' => '20.00']],
            'transaction_details' => ['net_received_amount' => '830.00'],
            'transaction_amount_refunded' => '0.00',
            'date_approved' => now()->toIso8601String(),
            'date_last_updated' => now()->toIso8601String(),
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
