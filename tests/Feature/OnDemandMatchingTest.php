<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\JobStatus;
use App\Enums\ServiceMode;
use App\Models\Category;
use App\Models\JobRequest;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use App\Models\User;
use App\Services\OnDemandMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnDemandMatchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_immediate_request_matches_only_available_fresh_professionals_in_category_and_radius(): void
    {
        [$client, $category, $near, $far] = $this->matchingContext();
        $near->forceFill(['is_available' => true, 'availability_status' => AvailabilityStatus::AVAILABLE, 'last_latitude' => 19.4326, 'last_longitude' => -99.1332, 'location_updated_at' => now()])->save();
        $far->forceFill(['is_available' => true, 'last_latitude' => 19.60, 'last_longitude' => -99.13, 'location_updated_at' => now()])->save();

        $job = JobRequest::factory()->create([
            'client_id' => $client->id, 'professional_id' => null, 'category_id' => $category->id,
            'service_id' => null, 'service_mode' => ServiceMode::IMMEDIATE, 'status' => JobStatus::SEARCHING,
            'latitude' => 19.4327, 'longitude' => -99.1331, 'search_radius_km' => 5,
        ]);
        app(OnDemandMatchingService::class)->startSearch($job);

        $this->assertDatabaseHas('job_invitations', ['job_request_id' => $job->id, 'professional_id' => $near->id]);
        $this->assertDatabaseMissing('job_invitations', ['job_request_id' => $job->id, 'professional_id' => $far->id]);
    }

    public function test_location_staleness_and_availability_are_enforced(): void
    {
        [$client, $category, $near] = $this->matchingContext();
        Service::factory()->create(['professional_id' => $near->id, 'category_id' => $category->id]);
        $near->forceFill(['is_available' => false, 'location_updated_at' => now()->subHour(), 'last_latitude' => 19.4326, 'last_longitude' => -99.1332])->save();
        $job = JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => null, 'category_id' => $category->id, 'service_id' => null, 'service_mode' => ServiceMode::IMMEDIATE, 'status' => JobStatus::SEARCHING, 'latitude' => 19.4327, 'longitude' => -99.1331, 'search_radius_km' => 5]);

        app(OnDemandMatchingService::class)->startSearch($job);

        $this->assertDatabaseMissing('job_invitations', ['job_request_id' => $job->id]);
    }

    public function test_professional_can_accept_once_and_client_polling_does_not_expose_private_contact_data(): void
    {
        [$client, $category, $near, $far] = $this->matchingContext();
        foreach ([$near, $far] as $profile) {
            $profile->forceFill(['is_available' => true, 'last_latitude' => 19.4326, 'last_longitude' => -99.1332, 'location_updated_at' => now()])->save();
        }
        $job = JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => null, 'category_id' => $category->id, 'service_id' => null, 'service_mode' => ServiceMode::IMMEDIATE, 'status' => JobStatus::SEARCHING, 'latitude' => 19.4327, 'longitude' => -99.1331, 'address' => 'Calle privada 123', 'search_radius_km' => 5]);
        $matching = app(OnDemandMatchingService::class);
        $matching->startSearch($job);
        $invitation = $job->fresh()->invitations()->where('professional_id', $near->id)->firstOrFail();

        $response = $this->actingAs($client)->get(route('client.ondemand.status', $job));
        $response->assertOk()->assertJsonMissing(['address' => 'Calle privada 123'])->assertJsonPath('status', 'searching');
        $this->actingAs($near->user)->post(route('professional.opportunities.accept', $invitation))->assertRedirect();
        $this->assertSame(JobStatus::MATCHED, $job->fresh()->status);
        $this->assertSame($near->id, $job->fresh()->professional_id);
        $this->actingAs($far->user)->post(route('professional.opportunities.accept', $job->fresh()->invitations()->where('professional_id', $far->id)->firstOrFail()))->assertRedirect()->assertSessionHasErrors('invitation');
    }

    public function test_on_demand_quote_uses_existing_payment_workflow_states(): void
    {
        [$client, $category, $near] = $this->matchingContext();
        $near->forceFill(['is_available' => true, 'last_latitude' => 19.4326, 'last_longitude' => -99.1332, 'location_updated_at' => now()])->save();
        $job = JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => null, 'category_id' => $category->id, 'service_id' => null, 'service_mode' => ServiceMode::IMMEDIATE, 'status' => JobStatus::SEARCHING, 'latitude' => 19.4327, 'longitude' => -99.1331, 'search_radius_km' => 5]);
        $matching = app(OnDemandMatchingService::class);
        $matching->startSearch($job);
        $invitation = $job->fresh()->invitations()->firstOrFail();
        $matching->acceptInvitation($invitation, $near->user);

        $this->actingAs($near->user)->post(route('job-quotes.store', $job), ['amount' => 800, 'description' => 'Incluye mano de obra.'])->assertRedirect();
        $quote = $job->fresh()->quotes()->firstOrFail();
        $this->assertSame(JobStatus::AWAITING_QUOTE, $job->fresh()->status);
        $this->actingAs($client)->post(route('job-quotes.accept', $quote))->assertRedirect();
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->fresh()->status);
    }

    public function test_scheduled_request_preserves_date_and_slot_without_entering_immediate_search(): void
    {
        $client = User::factory()->client()->create();
        $category = Category::factory()->create();
        $this->actingAs($client)->post(route('client.scheduled.store'), [
            'category_id' => $category->id, 'title' => 'Pintar una recámara', 'description' => 'Necesito pintar una recámara completa.',
            'scheduled_for' => now()->addDays(2)->format('Y-m-d H:i:s'), 'scheduled_slot' => '11:00-14:00',
            'address' => 'Avenida Reforma 1', 'city' => 'Puebla', 'state' => 'Puebla', 'postal_code' => '72000',
        ])->assertRedirect();
        $job = JobRequest::query()->latest('id')->firstOrFail();
        $this->assertSame(ServiceMode::SCHEDULED, $job->service_mode);
        $this->assertSame('11:00-14:00', $job->scheduled_slot);
        $this->assertSame(JobStatus::PENDING, $job->status);
    }

    public function test_search_expires_can_be_started_again_and_can_be_cancelled(): void
    {
        [$client, $category, $near] = $this->matchingContext();
        $near->forceFill(['is_available' => true, 'last_latitude' => 19.4326, 'last_longitude' => -99.1332, 'location_updated_at' => now()])->save();
        $job = JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => null, 'category_id' => $category->id, 'service_id' => null, 'service_mode' => ServiceMode::IMMEDIATE, 'status' => JobStatus::SEARCHING, 'latitude' => 19.4327, 'longitude' => -99.1331, 'search_radius_km' => 5]);
        $matching = app(OnDemandMatchingService::class);
        $matching->startSearch($job);
        $job->forceFill(['search_expires_at' => now()->subSecond()])->save();
        $matching->refresh($job);
        $this->assertSame(JobStatus::EXPIRED, $job->fresh()->status);
        $matching->searchAgain($job->fresh(), $client);
        $this->assertSame(JobStatus::SEARCHING, $job->fresh()->status);
        $matching->cancelSearch($job->fresh(), $client);
        $this->assertSame(JobStatus::CANCELLED, $job->fresh()->status);
    }

    public function test_paid_job_supports_on_the_way_arrival_and_start_in_order(): void
    {
        [$client, , $near] = $this->matchingContext();
        $job = JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => $near->id, 'status' => JobStatus::PAID]);
        $this->actingAs($near->user)->post(route('job-requests.on-the-way', $job))->assertRedirect();
        $this->assertSame(JobStatus::ON_THE_WAY, $job->fresh()->status);
        $this->actingAs($near->user)->post(route('job-requests.arrive', $job))->assertRedirect();
        $this->assertSame(JobStatus::ARRIVED, $job->fresh()->status);
        $this->actingAs($near->user)->post(route('job-requests.start', $job))->assertRedirect();
        $this->assertSame(JobStatus::IN_PROGRESS, $job->fresh()->status);
    }

    private function matchingContext(): array
    {
        $client = User::factory()->client()->create();
        $category = Category::factory()->create();
        $near = ProfessionalProfile::factory()->create(['latitude' => 19.4326, 'longitude' => -99.1332]);
        $far = ProfessionalProfile::factory()->create(['latitude' => 19.60, 'longitude' => -99.13]);
        Service::factory()->create(['professional_id' => $near->id, 'category_id' => $category->id]);
        Service::factory()->create(['professional_id' => $far->id, 'category_id' => $category->id]);

        return [$client, $category, $near, $far];
    }
}
