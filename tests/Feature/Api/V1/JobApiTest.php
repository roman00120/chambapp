<?php

namespace Tests\Feature\Api\V1;

use App\Enums\AvailabilityStatus;
use App\Enums\InvitationStatus;
use App\Enums\JobStatus;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Enums\ServiceMode;
use App\Enums\UserStatus;
use App\Models\Category;
use App\Models\JobInvitation;
use App\Models\JobQuote;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_list_only_own_jobs_with_pagination_order_and_privacy(): void
    {
        $client = User::factory()->client()->create();
        $otherClient = User::factory()->client()->create();
        $category = Category::factory()->create();
        $professional = ProfessionalProfile::factory()->create();

        $older = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'category_id' => $category->id,
            'status' => JobStatus::SEARCHING,
            'created_at' => now()->subHour(),
            'address' => 'Dirección privada 123',
            'latitude' => '20.6700000',
            'longitude' => '-103.3400000',
        ]);
        $newer = JobRequest::factory()->completed()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'category_id' => $category->id,
            'created_at' => now(),
        ]);
        JobRequest::factory()->create(['client_id' => $otherClient->id]);

        Sanctum::actingAs($client);
        $this->getJson('/api/v1/jobs')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $older->id)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.1.category.id', $category->id)
            ->assertJsonPath('data.1.professional.id', $professional->id)
            ->assertJsonMissingPath('data.1.address')
            ->assertJsonMissingPath('data.1.latitude')
            ->assertJsonMissingPath('data.1.longitude')
            ->assertJsonMissingPath('data.1.professional.phone')
            ->assertJsonMissingPath('data.1.professional.email');
    }

    public function test_job_list_requires_client_role_and_valid_status_filter(): void
    {
        $this->getJson('/api/v1/jobs')->assertUnauthorized();

        // In multimode, an active professional can also act as a client.
        // Use a suspended professional to verify the role gate truly blocks inactive users.
        $suspendedPro = User::factory()->professional()->create(['status' => UserStatus::SUSPENDED]);
        Sanctum::actingAs($suspendedPro);
        $this->getJson('/api/v1/jobs')->assertForbidden();

        $client = User::factory()->client()->create();
        JobRequest::factory()->create(['client_id' => $client->id, 'status' => JobStatus::SEARCHING]);
        JobRequest::factory()->completed()->create(['client_id' => $client->id]);
        Sanctum::actingAs($client);

        $this->getJson('/api/v1/jobs?status=searching')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'searching');
        $this->getJson('/api/v1/jobs?status=not-a-status')->assertUnprocessable();
    }

    public function test_professional_can_list_only_own_jobs_with_pagination_order_filter_and_resource(): void
    {
        $this->getJson('/api/v1/professional/jobs')->assertUnauthorized();

        $client = User::factory()->client()->create();
        Sanctum::actingAs($client);
        $this->getJson('/api/v1/professional/jobs')->assertForbidden();

        $professional = ProfessionalProfile::factory()->create();
        $otherProfessional = ProfessionalProfile::factory()->create();
        $category = Category::factory()->create();
        $older = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'category_id' => $category->id,
            'status' => JobStatus::PAID,
            'created_at' => now()->subHour(),
        ]);
        $newer = JobRequest::factory()->completed()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'category_id' => $category->id,
            'created_at' => now(),
        ]);
        JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $otherProfessional->id,
            'created_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($professional->user);
        $this->getJson('/api/v1/professional/jobs?professional_id='.$otherProfessional->id)
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $older->id)
            ->assertJsonPath('data.1.category.id', $category->id)
            ->assertJsonPath('data.1.professional.id', $professional->id)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonStructure(['data', 'links', 'meta']);

        $this->getJson('/api/v1/professional/jobs?status=paid')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $older->id)
            ->assertJsonPath('data.0.status', 'paid');
        $this->getJson('/api/v1/professional/jobs?status=not-a-status')->assertUnprocessable();
    }

    public function test_professional_job_list_preserves_job_resource_payment_privacy(): void
    {
        $client = User::factory()->client()->create([
            'name' => 'Cliente Privado',
            'email' => 'cliente-privado@example.test',
            'phone' => '5512345678',
        ]);
        $professional = ProfessionalProfile::factory()->create();
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'address' => 'Dirección privada 123',
            'postal_code' => '01000',
            'latitude' => '20.6700000',
            'longitude' => '-103.3400000',
        ]);

        Sanctum::actingAs($professional->user);
        $prePayment = $this->getJson('/api/v1/professional/jobs')->assertOk();
        $prePayment
            ->assertJsonPath('data.0.id', $job->id)
            ->assertJsonMissingPath('data.0.address')
            ->assertJsonMissingPath('data.0.postal_code')
            ->assertJsonMissingPath('data.0.latitude')
            ->assertJsonMissingPath('data.0.longitude');
        $this->assertStringNotContainsString('cliente-privado@example.test', $prePayment->getContent());
        $this->assertStringNotContainsString('5512345678', $prePayment->getContent());
        $this->assertStringNotContainsString('WhatsApp', $prePayment->getContent());

        Payment::factory()->create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => PaymentStatus::APPROVED,
        ]);

        $postPayment = $this->getJson('/api/v1/professional/jobs')->assertOk();
        $postPayment
            ->assertJsonPath('data.0.address', 'Dirección privada 123')
            ->assertJsonPath('data.0.postal_code', '01000')
            ->assertJsonPath('data.0.latitude', '20.6700000')
            ->assertJsonPath('data.0.longitude', '-103.3400000');
        $this->assertStringNotContainsString('cliente-privado@example.test', $postPayment->getContent());
        $this->assertStringNotContainsString('5512345678', $postPayment->getContent());
    }

    public function test_client_can_create_immediate_job_and_poll_status(): void
    {
        $client = User::factory()->client()->create();
        $category = Category::factory()->create();
        Sanctum::actingAs($client);

        $response = $this->postJson('/api/v1/jobs/immediate', [
            'category_id' => $category->id,
            'description' => 'Tengo una fuga debajo del fregadero.',
            'latitude' => 20.67,
            'longitude' => -103.34,
            'address' => 'Dirección privada 123',
        ])->assertCreated()->assertJsonPath('data.status', 'searching');

        $jobId = $response->json('data.id');
        $this->getJson('/api/v1/jobs/'.$jobId.'/status')
            ->assertOk()
            ->assertJsonPath('data.status', 'searching');
    }

    public function test_job_detail_prevents_idor_and_hides_exact_location_until_payment(): void
    {
        $client = User::factory()->client()->create();
        $otherClient = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'address' => 'Privada 123',
            'latitude' => '20.6700000',
            'longitude' => '-103.3400000',
        ]);

        Sanctum::actingAs($client);
        $this->getJson('/api/v1/jobs/'.$job->id)
            ->assertOk()
            ->assertJsonMissingPath('data.address')
            ->assertJsonMissingPath('data.latitude')
            ->assertJsonMissingPath('data.longitude');

        Sanctum::actingAs($otherClient);
        $this->getJson('/api/v1/jobs/'.$job->id)->assertForbidden();

        Payment::factory()->create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => PaymentStatus::APPROVED,
        ]);
        Sanctum::actingAs($client);
        $this->getJson('/api/v1/jobs/'.$job->id)
            ->assertOk()
            ->assertJsonPath('data.address', 'Privada 123')
            ->assertJsonPath('data.latitude', '20.6700000');
    }

    public function test_availability_validates_coordinates_and_radius(): void
    {
        $professional = ProfessionalProfile::factory()->verifiedIdentity()->create();
        Sanctum::actingAs($professional->user);

        $this->putJson('/api/v1/professional/availability', [
            'is_available' => true,
            'latitude' => 91,
            'longitude' => -103.34,
            'service_radius_km' => 10,
        ])->assertUnprocessable();

        $this->putJson('/api/v1/professional/availability', [
            'is_available' => true,
            'latitude' => 20.67,
            'longitude' => -103.34,
            'service_radius_km' => 10,
        ])->assertOk()->assertJsonPath('data.is_available', true);
    }

    public function test_exactly_one_professional_wins_an_invitation_and_second_gets_stable_conflict_code(): void
    {
        $client = User::factory()->client()->create();
        $category = Category::factory()->create();
        $first = $this->availableProfessional();
        $second = $this->availableProfessional();
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => null,
            'category_id' => $category->id,
            'service_mode' => ServiceMode::IMMEDIATE,
            'status' => JobStatus::SEARCHING,
            'latitude' => '20.6700000',
            'longitude' => '-103.3400000',
            'search_radius_km' => '10.00',
            'search_expires_at' => now()->addMinutes(5),
        ]);
        $firstInvitation = $this->invitation($job, $first);
        $secondInvitation = $this->invitation($job, $second);

        Sanctum::actingAs($first->user);
        $this->postJson('/api/v1/professional/job-invitations/'.$firstInvitation->id.'/accept')
            ->assertOk()->assertJsonPath('data.status', 'matched');

        Sanctum::actingAs($second->user);
        $this->postJson('/api/v1/professional/job-invitations/'.$secondInvitation->id.'/accept')
            ->assertStatus(409)->assertJsonPath('code', 'JOB_ALREADY_TAKEN');

        $this->assertSame($first->id, $job->fresh()->professional_id);
    }

    public function test_quotes_apply_contact_guard_and_client_can_accept_only_own_quote(): void
    {
        $client = User::factory()->client()->create();
        $other = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->verifiedIdentity()->create();
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => JobStatus::MATCHED,
        ]);

        Sanctum::actingAs($professional->user);
        $this->postJson('/api/v1/professional/jobs/'.$job->id.'/quotes', [
            'amount' => '1000.00',
            'description' => 'Escríbeme por WhatsApp al 5512345678',
        ])->assertUnprocessable();
        $quoteResponse = $this->postJson('/api/v1/professional/jobs/'.$job->id.'/quotes', [
            'amount' => '1000.00',
            'description' => 'Incluye mano de obra y materiales básicos.',
        ])->assertCreated();
        $quoteId = $quoteResponse->json('data.id');

        Sanctum::actingAs($other);
        $this->postJson('/api/v1/jobs/'.$job->id.'/quotes/'.$quoteId.'/accept')->assertForbidden();

        Sanctum::actingAs($client);
        $this->postJson('/api/v1/jobs/'.$job->id.'/quotes/'.$quoteId.'/accept')
            ->assertOk()->assertJsonPath('data.status', 'accepted');
        $this->assertSame('1000.00', $job->fresh()->agreed_price);
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->fresh()->status);
    }

    public function test_client_owner_can_reject_quote_and_existing_accept_action_still_works(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->verifiedIdentity()->create();
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => JobStatus::AWAITING_QUOTE,
        ]);
        $rejected = JobQuote::factory()->create([
            'job_request_id' => $job->id,
            'professional_id' => $professional->id,
            'status' => QuoteStatus::PENDING,
        ]);

        Sanctum::actingAs($client);
        $this->postJson('/api/v1/jobs/'.$job->id.'/quotes/'.$rejected->id.'/reject', [
            'reason' => 'price_high',
        ])->assertOk()->assertJsonPath('data.status', 'rejected');
        $this->assertSame(QuoteStatus::REJECTED, $rejected->fresh()->status);
        $this->assertSame(JobStatus::AWAITING_QUOTE, $job->fresh()->status);

        $accepted = JobQuote::factory()->create([
            'job_request_id' => $job->id,
            'professional_id' => $professional->id,
            'status' => QuoteStatus::PENDING,
        ]);
        $this->postJson('/api/v1/jobs/'.$job->id.'/quotes/'.$accepted->id.'/accept')
            ->assertOk()->assertJsonPath('data.status', 'accepted');
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->fresh()->status);
    }

    public function test_assigned_professional_can_requote_after_rejection_without_losing_history(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        $otherProfessional = ProfessionalProfile::factory()->create();
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => JobStatus::MATCHED,
        ]);

        Sanctum::actingAs($professional->user);
        $firstId = $this->postJson('/api/v1/professional/jobs/'.$job->id.'/quotes', [
            'amount' => '1000.00',
            'description' => 'Primera propuesta con mano de obra.',
        ])->assertCreated()->assertJsonPath('data.status', 'pending')->json('data.id');

        Sanctum::actingAs($client);
        $this->postJson('/api/v1/jobs/'.$job->id.'/quotes/'.$firstId.'/reject', [
            'reason' => 'price_high',
        ])->assertOk()->assertJsonPath('data.status', 'rejected');
        $this->assertSame(JobStatus::AWAITING_QUOTE, $job->fresh()->status);

        Sanctum::actingAs($otherProfessional->user);
        $this->postJson('/api/v1/professional/jobs/'.$job->id.'/quotes', [
            'amount' => '900.00',
            'description' => 'Intento de un profesional ajeno.',
        ])->assertForbidden();

        Sanctum::actingAs($client);
        $this->postJson('/api/v1/professional/jobs/'.$job->id.'/quotes', [
            'amount' => '900.00',
            'description' => 'Intento incorrecto del cliente.',
        ])->assertForbidden();

        Sanctum::actingAs($professional->user);
        $secondId = $this->postJson('/api/v1/professional/jobs/'.$job->id.'/quotes', [
            'amount' => '900.00',
            'description' => 'Segunda propuesta ajustada.',
        ])->assertCreated()->assertJsonPath('data.status', 'pending')->json('data.id');

        $this->assertNotSame($firstId, $secondId);
        $this->assertDatabaseHas('job_quotes', [
            'id' => $firstId,
            'job_request_id' => $job->id,
            'status' => QuoteStatus::REJECTED->value,
        ]);
        $this->assertDatabaseHas('job_quotes', [
            'id' => $secondId,
            'job_request_id' => $job->id,
            'status' => QuoteStatus::PENDING->value,
        ]);
        $this->assertSame(1, $job->quotes()->where('status', QuoteStatus::PENDING->value)->count());
    }

    public function test_terminal_jobs_do_not_accept_new_quotes(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        Sanctum::actingAs($professional->user);

        foreach ([JobStatus::PAID, JobStatus::IN_PROGRESS, JobStatus::COMPLETED, JobStatus::CANCELLED, JobStatus::DISPUTED, JobStatus::EXPIRED] as $status) {
            $job = JobRequest::factory()->create([
                'client_id' => $client->id,
                'professional_id' => $professional->id,
                'status' => $status,
            ]);
            $this->postJson('/api/v1/professional/jobs/'.$job->id.'/quotes', [
                'amount' => '900.00',
                'description' => 'Propuesta que el estado no permite.',
            ])->assertForbidden();
        }
    }

    public function test_new_quote_supersedes_only_previous_active_quote(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => JobStatus::AWAITING_QUOTE,
        ]);
        $previous = JobQuote::factory()->create([
            'job_request_id' => $job->id,
            'professional_id' => $professional->id,
            'status' => QuoteStatus::PENDING,
        ]);

        Sanctum::actingAs($professional->user);
        $this->postJson('/api/v1/professional/jobs/'.$job->id.'/quotes', [
            'amount' => '850.00',
            'description' => 'Propuesta vigente actualizada.',
        ])->assertCreated();

        $this->assertSame(QuoteStatus::SUPERSEDED, $previous->fresh()->status);
        $this->assertSame(1, $job->quotes()->where('status', QuoteStatus::PENDING->value)->count());
    }

    public function test_quote_rejection_rejects_other_clients_professionals_and_mismatched_job(): void
    {
        $client = User::factory()->client()->create();
        $otherClient = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => JobStatus::AWAITING_QUOTE,
        ]);
        $otherJob = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => JobStatus::AWAITING_QUOTE,
        ]);
        $quote = JobQuote::factory()->create([
            'job_request_id' => $job->id,
            'professional_id' => $professional->id,
            'status' => QuoteStatus::PENDING,
        ]);
        $uri = '/api/v1/jobs/'.$job->id.'/quotes/'.$quote->id.'/reject';

        Sanctum::actingAs($otherClient);
        $this->postJson($uri, ['reason' => 'price_high'])->assertForbidden();
        Sanctum::actingAs($professional->user);
        $this->postJson($uri, ['reason' => 'price_high'])->assertForbidden();
        Sanctum::actingAs($client);
        $this->postJson('/api/v1/jobs/'.$otherJob->id.'/quotes/'.$quote->id.'/reject', [
            'reason' => 'price_high',
        ])->assertNotFound();
        $this->postJson('/api/v1/jobs/'.$job->id.'/quotes/999999/reject', [
            'reason' => 'price_high',
        ])->assertNotFound();
    }

    public function test_processed_quote_cannot_be_rejected_again(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => JobStatus::AWAITING_PAYMENT,
        ]);

        Sanctum::actingAs($client);
        foreach ([QuoteStatus::ACCEPTED, QuoteStatus::REJECTED] as $status) {
            $quote = JobQuote::factory()->create([
                'job_request_id' => $job->id,
                'professional_id' => $professional->id,
                'status' => $status,
            ]);
            $this->postJson('/api/v1/jobs/'.$job->id.'/quotes/'.$quote->id.'/reject', [
                'reason' => 'price_high',
            ])->assertForbidden();
        }
    }

    public function test_checkout_ignores_device_amount_and_calculates_fifteen_percent_server_side(): void
    {
        Http::fake(['*' => Http::response([
            'id' => 'pref-123',
            'init_point' => 'https://www.mercadopago.com/checkout',
            'sandbox_init_point' => 'https://sandbox.mercadopago.com/checkout',
        ])]);
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create([
            'mercadopago_user_id' => 'seller-1',
            'mercadopago_access_token' => 'server-secret',
            'mercadopago_token_expires_at' => now()->addDay(),
        ]);
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => JobStatus::AWAITING_PAYMENT,
            'agreed_price' => '1000.00',
        ]);
        JobQuote::factory()->create([
            'job_request_id' => $job->id,
            'professional_id' => $professional->id,
            'amount' => '1000.00',
            'status' => QuoteStatus::ACCEPTED,
        ]);

        Sanctum::actingAs($client);
        $this->postJson('/api/v1/jobs/'.$job->id.'/checkout', ['amount' => '1.00'])
            ->assertOk()
            ->assertJsonPath('data.payment.gross_amount', '1000.00')
            ->assertJsonPath('data.payment.platform_fee_percent', '15.00')
            ->assertJsonPath('data.payment.platform_fee', '150.00')
            ->assertJsonPath('data.payment.professional_amount', '850.00');
    }

    public function test_paid_job_workflow_and_review_complete_via_api(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->verifiedIdentity()->create();
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => JobStatus::PAID,
        ]);

        Sanctum::actingAs($professional->user);
        $this->postJson('/api/v1/jobs/'.$job->id.'/on-the-way')->assertOk()->assertJsonPath('data.status', 'on_the_way');
        $this->postJson('/api/v1/jobs/'.$job->id.'/arrived')->assertOk()->assertJsonPath('data.status', 'arrived');
        $this->postJson('/api/v1/jobs/'.$job->id.'/start')->assertOk()->assertJsonPath('data.status', 'in_progress');
        $this->postJson('/api/v1/jobs/'.$job->id.'/finish')->assertOk()->assertJsonPath('data.status', 'awaiting_confirmation');
        $code = $job->fresh()->completion_code;

        Sanctum::actingAs($client);
        $this->postJson('/api/v1/jobs/'.$job->id.'/confirm', ['completion_code' => $code])
            ->assertOk()->assertJsonPath('data.status', 'completed');
        $review = $this->postJson('/api/v1/jobs/'.$job->id.'/review', ['rating' => 5, 'comment' => '<script>alert(1)</script> Excelente'])
            ->assertCreated()->assertJsonPath('data.rating', 5);
        $review->assertJsonPath('data.comment', '<script>alert(1)</script> Excelente');
        $this->assertSame(5, $professional->fresh()->total_reviews > 0 ? (int) $professional->fresh()->average_rating : 0);
    }

    public function test_complete_e2e_direct_service_hiring_and_quote_acceptance_flow(): void
    {
        Http::fake(['*' => Http::response([
            'id' => 'pref-direct-123',
            'init_point' => 'https://www.mercadopago.com/checkout',
            'sandbox_init_point' => 'https://sandbox.mercadopago.com/checkout',
        ])]);
        $client = User::factory()->client()->create(['name' => 'Kevin Cliente']);
        $proUser = User::factory()->professional()->create(['name' => 'Romy Pro']);
        $proProfile = ProfessionalProfile::factory()->for($proUser)->verifiedIdentity()->create([
            'is_available' => true,
            'availability_status' => AvailabilityStatus::AVAILABLE,
            'mercadopago_user_id' => '79786415',
            'mercadopago_access_token' => 'server-secret-pro',
            'mercadopago_token_expires_at' => now()->addDay(),
        ]);
        $category = Category::factory()->create(['name' => 'Informática (mantenimiento PC)', 'is_active' => true]);
        $service = \App\Models\Service::factory()->create([
            'professional_id' => $proProfile->id,
            'category_id' => $category->id,
            'title' => 'prueba prueba',
            'price' => '200.00',
            'is_active' => true,
        ]);

        // 1. Cliente envía solicitud directa para el servicio 'prueba prueba'
        Sanctum::actingAs($client);
        $payload = [
            'category_id' => $category->id,
            'service_id' => $service->id,
            'title' => 'prueba prueba',
            'description' => 'Requiero contratar su servicio de mantenimiento de PC.',
            'scheduled_for' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'scheduled_slot' => '11:00-14:00',
            'address' => 'Av. Vallarta 1234',
            'city' => 'Guadalajara',
            'state' => 'Jalisco',
            'postal_code' => '44100',
        ];

        $response = $this->postJson('/api/v1/jobs/scheduled', $payload)->assertCreated();

        // 2. Verificaciones del Job Request creado (directo a awaiting_payment sin radar ni cotizaciones)
        $response->assertJsonPath('data.service.id', $service->id);
        $response->assertJsonPath('data.service.title', 'prueba prueba');
        $response->assertJsonPath('data.professional.id', $proProfile->id);
        $response->assertJsonPath('data.status', 'awaiting_payment');
        $response->assertJsonPath('data.status_label', 'Esperando pago');
        $response->assertJsonPath('data.economic_breakdown.base_amount', '200.00');
        $response->assertJsonPath('data.economic_breakdown.client_service_fee', '30.00');
        $response->assertJsonPath('data.economic_breakdown.customer_total', '230.00');

        $jobId = $response->json('data.id');
        $job = JobRequest::query()->find($jobId);
        $this->assertNotNull($job);
        $this->assertSame($service->id, $job->service_id);
        $this->assertSame($proProfile->id, $job->professional_id);
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->status);
        $this->assertDatabaseCount('job_invitations', 0); // Matching/Radar NO ejecutado
        $this->assertDatabaseCount('job_quotes', 0); // NO requiere cotizaciones

        // 3. Cliente procede directamente al checkout sin cotizaciones
        $checkoutResponse = $this->postJson("/api/v1/jobs/{$job->id}/checkout")
            ->assertOk()
            ->assertJsonPath('data.checkout_url', 'https://sandbox.mercadopago.com/checkout')
            ->assertJsonPath('data.payment.base_amount', '200.00')
            ->assertJsonPath('data.payment.client_service_fee', '30.00')
            ->assertJsonPath('data.payment.customer_total', '230.00');

        $calc = app(\App\Services\PaymentCalculationService::class)->forJob($job);
        $this->assertSame('200.00', $calc->baseAmount);
        $this->assertSame('30.00', $calc->clientServiceFee);
        $this->assertSame('230.00', $calc->customerTotal);
        $this->assertSame('60.00', $calc->platformGrossFee);
        $this->assertSame('170.00', $calc->professionalAmountBeforeExternalCosts);
    }

    public function test_service_with_price_50_creates_job_with_exact_15_percent_breakdown_and_checkout_57_50(): void
    {
        $proUser = User::factory()->professional()->create();
        $proProfile = ProfessionalProfile::factory()->verifiedIdentity()->create([
            'user_id' => $proUser->id,
            'is_available' => true,
            'availability_status' => AvailabilityStatus::AVAILABLE,
            'mercadopago_user_id' => '79786415',
            'mercadopago_access_token' => 'TEST-seller-token',
            'mercadopago_refresh_token' => 'TEST-seller-refresh',
            'mercadopago_token_expires_at' => now()->addMonths(5),
        ]);
        $client = User::factory()->client()->create();
        $category = Category::factory()->create(['name' => 'Informática (mantenimiento PC)', 'is_active' => true]);
        $service = \App\Models\Service::factory()->create([
            'professional_id' => $proProfile->id,
            'category_id' => $category->id,
            'title' => 'Informática (mantenimiento PC)',
            'price' => '50.00',
            'is_active' => true,
        ]);

        Sanctum::actingAs($client);
        $payload = [
            'category_id' => $category->id,
            'service_id' => $service->id,
            'title' => 'Informática (mantenimiento PC)',
            'description' => 'Servicio directo de mantenimiento de PC desde el catálogo.',
            'scheduled_for' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'scheduled_slot' => '11:00-14:00',
            'address' => 'Av. Hidalgo 456',
            'city' => 'Guadalajara',
            'state' => 'Jalisco',
            'postal_code' => '44100',
        ];

        // 1. Cliente contrata servicio de $50
        $storeResponse = $this->postJson('/api/v1/jobs/scheduled', $payload)->assertCreated();
        $jobId = $storeResponse->json('data.id');

        $storeResponse->assertJsonPath('data.status', 'awaiting_payment')
            ->assertJsonPath('data.agreed_price', '50.00')
            ->assertJsonPath('data.service.price', '50.00')
            ->assertJsonPath('data.economic_breakdown.base_amount', '50.00')
            ->assertJsonPath('data.economic_breakdown.client_service_fee', '7.50')
            ->assertJsonPath('data.economic_breakdown.customer_total', '57.50');

        // 2. GET /api/v1/jobs/{id} devuelve exactamente los mismos valores
        $showResponse = $this->getJson("/api/v1/jobs/{$jobId}")->assertOk();
        $showResponse->assertJsonPath('data.status', 'awaiting_payment')
            ->assertJsonPath('data.agreed_price', '50.00')
            ->assertJsonPath('data.service.price', '50.00')
            ->assertJsonPath('data.economic_breakdown.base_amount', '50.00')
            ->assertJsonPath('data.economic_breakdown.client_service_fee', '7.50')
            ->assertJsonPath('data.economic_breakdown.customer_total', '57.50');

        // 3. Checkout genera el pago por $57.50
        Http::fake([
            'https://api.mercadopago.com/checkout/preferences*' => Http::response([
                'id' => 'pref-test-50',
                'init_point' => 'https://www.mercadopago.com.mx/checkout/v1/redirect?pref_id=pref-test-50',
                'sandbox_init_point' => 'https://sandbox.mercadopago.com.mx/checkout/v1/redirect?pref_id=pref-test-50',
            ], 201),
        ]);

        $checkoutResponse = $this->postJson("/api/v1/jobs/{$jobId}/checkout")->assertOk();
        $checkoutResponse->assertJsonPath('data.payment.customer_total', '57.50')
            ->assertJsonPath('data.payment.base_amount', '50.00')
            ->assertJsonPath('data.payment.client_service_fee', '7.50')
            ->assertJsonPath('data.payment.client_service_fee_percent', '15.00');

        $payment = Payment::query()->where('job_request_id', $jobId)->first();
        $this->assertNotNull($payment);
        $this->assertSame('57.50', (string) $payment->gross_amount);
        $this->assertSame('57.50', (string) $payment->customer_total);
        $this->assertSame('50.00', (string) $payment->base_amount);
        $this->assertSame('7.50', (string) $payment->client_service_fee);
        $this->assertSame('15.00', (string) $payment->client_service_fee_percent);
    }

    private function availableProfessional(): ProfessionalProfile
    {
        return ProfessionalProfile::factory()->verifiedIdentity()->create([
            'is_available' => true,
            'availability_status' => AvailabilityStatus::AVAILABLE,
            'last_latitude' => '20.6700000',
            'last_longitude' => '-103.3400000',
            'location_updated_at' => now(),
            'service_radius_km' => 10,
        ]);
    }

    private function invitation(JobRequest $job, ProfessionalProfile $professional): JobInvitation
    {
        return JobInvitation::query()->create([
            'job_request_id' => $job->id,
            'professional_id' => $professional->id,
            'distance_km' => '1.00',
            'status' => InvitationStatus::PENDING,
            'invited_at' => now(),
            'expires_at' => now()->addMinutes(3),
        ]);
    }
}
