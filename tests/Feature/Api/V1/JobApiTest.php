<?php

namespace Tests\Feature\Api\V1;

use App\Enums\AvailabilityStatus;
use App\Enums\InvitationStatus;
use App\Enums\JobStatus;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Enums\ServiceMode;
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
        $professional = ProfessionalProfile::factory()->create();
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
        $professional = ProfessionalProfile::factory()->create();
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
        $professional = ProfessionalProfile::factory()->create();
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

    private function availableProfessional(): ProfessionalProfile
    {
        return ProfessionalProfile::factory()->create([
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
