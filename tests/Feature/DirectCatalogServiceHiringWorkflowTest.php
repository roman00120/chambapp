<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\IdentityVerificationStatus;
use App\Enums\JobStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use App\Models\User;
use App\Notifications\DirectServiceRequestedNotification;
use App\Notifications\PaymentConfirmedProfessionalNotification;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DirectCatalogServiceHiringWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_e_f_g_k_direct_catalog_service_hiring_creates_job_awaiting_payment_without_radar_or_quotes(): void
    {
        Notification::fake();

        $client = User::factory()->client()->create(['name' => 'Cliente Catalog']);
        $proUser = User::factory()->professional()->create(['name' => 'Pro Catalog']);
        $proProfile = ProfessionalProfile::factory()->for($proUser)->create([
            'is_available' => true,
            'availability_status' => AvailabilityStatus::AVAILABLE,
            'mercadopago_user_id' => 'mp-pro-123',
            'mercadopago_access_token' => 'token-pro-123',
            'mercadopago_token_expires_at' => now()->addMonth(),
        ]);
        $category = Category::factory()->create(['name' => 'Plomería', 'is_active' => true]);
        $service = Service::factory()->create([
            'professional_id' => $proProfile->id,
            'category_id' => $category->id,
            'title' => 'Instalación de Calentador Solar',
            'price' => '1500.00',
            'is_active' => true,
        ]);

        Sanctum::actingAs($client);
        $payload = [
            'category_id' => $category->id,
            'service_id' => $service->id,
            'title' => 'Instalación de Calentador Solar',
            'description' => 'Requiero instalación completa en azotea.',
            'scheduled_for' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'scheduled_slot' => '11:00-14:00',
            'address' => 'Av. Hidalgo 456',
            'city' => 'Guadalajara',
            'state' => 'Jalisco',
            'postal_code' => '44100',
        ];

        // POST /api/v1/jobs/scheduled
        $response = $this->postJson('/api/v1/jobs/scheduled', $payload)->assertCreated();

        $jobId = $response->json('data.id');
        $job = JobRequest::query()->find($jobId);

        // Assertions for E, F, G, K:
        $this->assertNotNull($job);
        $this->assertSame($service->id, $job->service_id);
        $this->assertSame($proProfile->id, $job->professional_id);
        $this->assertSame($client->id, $job->client_id);
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->status);

        // G) uses service.price (1500.00)
        $this->assertSame('1500.00', (string) $job->base_amount);
        $this->assertSame('1500.00', (string) $job->agreed_price);
        $this->assertSame('225.00', (string) $job->client_service_fee); // 15%
        $this->assertSame('1725.00', (string) $job->customer_total); // 1500 + 225
        $this->assertSame('450.00', (string) $job->platform_gross_fee); // 30%
        $this->assertSame('1275.00', (string) $job->professional_amount_before_external_costs); // 1500 - 225

        // E) No JobQuote created
        $this->assertDatabaseCount('job_quotes', 0);
        // F) No Radar / Matching invitations
        $this->assertDatabaseCount('job_invitations', 0);

        // K) Resource contains valid economic breakdown
        $response->assertJsonPath('data.economic_breakdown.base_amount', '1500.00');
        $response->assertJsonPath('data.economic_breakdown.client_service_fee', '225.00');
        $response->assertJsonPath('data.economic_breakdown.customer_total', '1725.00');

        // Professional receives DirectServiceRequestedNotification
        Notification::assertSentTo($proUser, DirectServiceRequestedNotification::class);
    }

    public function test_a_b_c_d_roles_and_active_mode_for_checkout(): void
    {
        Http::fake(function () {
            static $i = 1;

            return Http::response([
                'id' => 'pref-test-'.($i++),
                'init_point' => 'https://www.mercadopago.com/checkout',
                'sandbox_init_point' => 'https://sandbox.mercadopago.com/checkout',
            ]);
        });

        $proUser = User::factory()->professional()->create(['name' => 'Pro Asignado']);
        $proProfile = ProfessionalProfile::factory()->for($proUser)->create([
            'is_available' => true,
            'availability_status' => AvailabilityStatus::AVAILABLE,
            'mercadopago_user_id' => 'mp-seller-456',
            'mercadopago_access_token' => 'token-456',
            'mercadopago_token_expires_at' => now()->addMonth(),
        ]);
        $proProfile->identityVerification()->create(['status' => IdentityVerificationStatus::VERIFIED]);
        $service = Service::factory()->create([
            'professional_id' => $proProfile->id,
            'price' => '800.00',
            'is_active' => true,
        ]);

        // A) Cliente normal
        $normalClient = User::factory()->client()->create();
        $jobA = app(\App\Services\JobRequestService::class)->createScheduled($normalClient, [
            'service_id' => $service->id,
            'category_id' => $service->category_id,
            'title' => $service->title,
            'description' => 'Solicitud cliente normal',
            'address' => 'Calle 1',
            'city' => 'Guadalajara',
            'state' => 'Jalisco',
            'postal_code' => '44100',
            'scheduled_for' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'scheduled_slot' => '11:00-14:00',
        ]);
        Sanctum::actingAs($normalClient);
        $this->postJson("/api/v1/jobs/{$jobA->id}/checkout")
            ->assertOk()
            ->assertJsonPath('data.payment.customer_total', '920.00');

        // B) Cliente con role=professional + activeMode=client
        $proAsClient = User::factory()->professional()->create();
        $jobB = app(\App\Services\JobRequestService::class)->createScheduled($proAsClient, [
            'service_id' => $service->id,
            'category_id' => $service->category_id,
            'title' => $service->title,
            'description' => 'Solicitud pro as client',
            'address' => 'Calle 2',
            'city' => 'Guadalajara',
            'state' => 'Jalisco',
            'postal_code' => '44100',
            'scheduled_for' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'scheduled_slot' => '11:00-14:00',
        ]);
        Sanctum::actingAs($proAsClient);
        $this->postJson("/api/v1/jobs/{$jobB->id}/checkout")
            ->assertOk()
            ->assertJsonPath('data.payment.customer_total', '920.00');

        // C) Cliente con role=admin + activeMode=client
        $adminAsClient = User::factory()->admin()->create();
        $jobC = app(\App\Services\JobRequestService::class)->createScheduled($adminAsClient, [
            'service_id' => $service->id,
            'category_id' => $service->category_id,
            'title' => $service->title,
            'description' => 'Solicitud admin as client',
            'address' => 'Calle 3',
            'city' => 'Guadalajara',
            'state' => 'Jalisco',
            'postal_code' => '44100',
            'scheduled_for' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'scheduled_slot' => '11:00-14:00',
        ]);
        Sanctum::actingAs($adminAsClient);
        $this->postJson("/api/v1/jobs/{$jobC->id}/checkout")
            ->assertOk()
            ->assertJsonPath('data.payment.customer_total', '920.00');

        // D) Profesional asignado -> NO puede pagar el trabajo (403 forbidden)
        Sanctum::actingAs($proUser);
        $this->postJson("/api/v1/jobs/{$jobA->id}/checkout")
            ->assertForbidden();
    }

    public function test_h_i_j_approved_payment_transitions_job_to_paid_and_notifies_and_emails_professional(): void
    {
        Notification::fake();

        $client = User::factory()->client()->create(['name' => 'Cliente Pagador']);
        $proUser = User::factory()->professional()->create(['name' => 'Pro Pagado', 'email' => 'pro.pagado@example.test']);
        $proProfile = ProfessionalProfile::factory()->for($proUser)->create([
            'is_available' => true,
            'availability_status' => AvailabilityStatus::AVAILABLE,
            'mercadopago_user_id' => 'mp-pro-789',
            'mercadopago_access_token' => 'token-pro-789',
            'mercadopago_token_expires_at' => now()->addMonth(),
        ]);
        $proProfile->identityVerification()->create(['status' => IdentityVerificationStatus::VERIFIED]);

        $service = Service::factory()->create([
            'professional_id' => $proProfile->id,
            'price' => '1000.00',
            'is_active' => true,
        ]);

        $job = app(\App\Services\JobRequestService::class)->createScheduled($client, [
            'service_id' => $service->id,
            'category_id' => $service->category_id,
            'title' => $service->title,
            'description' => 'Servicio para pagar',
            'address' => 'Av. Juarez 100',
            'city' => 'Guadalajara',
            'state' => 'Jalisco',
            'postal_code' => '44100',
            'scheduled_for' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'scheduled_slot' => '11:00-14:00',
        ]);

        Http::fake(['*' => Http::response([
            'id' => 'pref-mp-789',
            'init_point' => 'https://www.mercadopago.com/checkout',
            'sandbox_init_point' => 'https://sandbox.mercadopago.com/checkout',
        ])]);

        $paymentService = app(PaymentService::class);
        $payment = $paymentService->startCheckout($job, $client);

        // Simulamos procesamiento de webhook de pago aprobado desde Mercado Pago
        $updatedPayment = $paymentService->applyProviderPayment($payment, [
            'id' => '123456789',
            'status' => 'approved',
            'external_reference' => $payment->external_reference,
            'transaction_amount' => '1150.00',
            'currency_id' => 'MXN',
            'live_mode' => false,
            'collector_id' => 'mp-pro-789',
            'fee_details' => [
                ['type' => 'mercadopago_fee', 'amount' => '35.00'],
            ],
            'date_approved' => now()->toIso8601String(),
        ], 'evt-test-123', ['raw' => 'audit']);

        // H) Job pasa a paid
        $this->assertEquals(PaymentStatus::APPROVED, $updatedPayment->status);
        $this->assertEquals(JobStatus::PAID, $job->fresh()->status);

        // I) & J) Profesional recibe notificación por BD y correo
        Notification::assertSentTo($proUser, PaymentConfirmedProfessionalNotification::class, function ($notification) use ($proUser) {
            $mail = $notification->toMail($proUser);
            $this->assertStringContainsString('El cliente realizó el pago', $mail->subject);
            return true;
        });
    }

    public function test_web_direct_catalog_service_hiring_creates_job_awaiting_payment_and_redirects_to_payment_summary(): void
    {
        Notification::fake();

        $client = User::factory()->client()->create(['name' => 'Cliente Web']);
        $proUser = User::factory()->professional()->create(['name' => 'Pro Web']);
        $proProfile = ProfessionalProfile::factory()->for($proUser)->create([
            'is_available' => true,
            'availability_status' => AvailabilityStatus::AVAILABLE,
            'verification_status' => VerificationStatus::VERIFIED,
            'mercadopago_user_id' => 'mp-web-123',
            'mercadopago_access_token' => 'token-web-123',
            'mercadopago_token_expires_at' => now()->addMonth(),
        ]);
        $proProfile->identityVerification()->create(['status' => IdentityVerificationStatus::VERIFIED]);

        $category = Category::factory()->create(['name' => 'Electricidad', 'is_active' => true]);
        $service = Service::factory()->create([
            'professional_id' => $proProfile->id,
            'category_id' => $category->id,
            'title' => 'Instalación de Lámparas',
            'price' => '500.00',
            'is_active' => true,
        ]);

        $this->actingAs($client);

        $response = $this->post(route('job-requests.store', $service), [
            'title' => 'Instalación de Lámparas',
            'description' => 'Instalación de 3 lámparas LED en sala y comedor.',
            'requested_date' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'address' => 'Calle Morelos 789',
            'city' => 'Zapopan',
            'state' => 'Jalisco',
            'postal_code' => '45000',
        ]);

        $job = JobRequest::query()->where('client_id', $client->id)->first();
        $this->assertNotNull($job);
        $this->assertSame($service->id, $job->service_id);
        $this->assertSame($proProfile->id, $job->professional_id);
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->status);
        $this->assertSame('500.00', (string) $job->base_amount);
        $this->assertSame('575.00', (string) $job->customer_total);

        // Sin JobQuote ni Radar
        $this->assertDatabaseCount('job_quotes', 0);
        $this->assertDatabaseCount('job_invitations', 0);

        // Redirige directamente al resumen de pago
        $response->assertRedirect(route('client.payments.summary', $job));

        // Profesional notificado
        Notification::assertSentTo($proUser, DirectServiceRequestedNotification::class);
    }
}
