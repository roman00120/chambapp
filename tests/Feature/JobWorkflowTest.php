<?php

namespace Tests\Feature;

use App\Enums\JobStatus;
use App\Enums\PriceType;
use App\Models\Category;
use App\Models\JobRequest;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_client_can_request_a_public_service_without_controlled_ids(): void
    {
        [$service, $professional] = $this->service();
        $client = User::factory()->client()->create();
        $date = now()->addDays(2)->setSeconds(0);

        $this->actingAs($client)->post(route('job-requests.store', $service), [
            'title' => 'Reparar la instalación',
            'description' => 'Necesito revisar una fuga en la cocina.',
            'requested_date' => $date->format('Y-m-d\\TH:i'),
            'address' => 'Calle del Trabajo 10',
            'city' => 'Puebla',
            'state' => 'Puebla',
            'postal_code' => '72000',
            'client_id' => User::factory()->client()->create()->id,
            'professional_id' => User::factory()->client()->create()->id,
            'status' => 'completed',
            'agreed_price' => 1,
        ])->assertRedirect();

        $job = JobRequest::query()->latest('id')->first();
        $this->assertNotNull($job);
        $this->assertSame($client->id, $job->client_id);
        $this->assertSame($professional->id, $job->professional_id);
        $this->assertSame($service->id, $job->service_id);
        $this->assertSame(JobStatus::PENDING, $job->status);
        $this->assertNull($job->agreed_price);
        $this->assertDatabaseHas('job_requests', ['id' => $job->id, 'title' => 'Reparar la instalación']);
    }

    public function test_visitors_are_redirected_and_professionals_cannot_request_their_own_service(): void
    {
        [$service, $professional] = $this->service();
        $this->get(route('job-requests.create', $service))->assertRedirect(route('login'));
        $this->actingAs($professional->user)->get(route('job-requests.create', $service))->assertForbidden();
        $this->actingAs($professional->user)->post(route('job-requests.store', $service), [])->assertForbidden();
    }

    public function test_inactive_service_cannot_be_requested(): void
    {
        [$service] = $this->service(['is_active' => false]);
        $client = User::factory()->client()->create();

        $this->actingAs($client)->get(route('job-requests.create', $service))->assertNotFound();
        $this->actingAs($client)->post(route('job-requests.store', $service), [
            'title' => 'Solicitud',
            'description' => 'Descripción válida.',
            'requested_date' => now()->addDay()->format('Y-m-d\\TH:i'),
            'address' => 'Calle 1',
            'city' => 'Puebla',
            'state' => 'Puebla',
            'postal_code' => '72000',
        ])->assertNotFound();
    }

    public function test_client_and_professional_lists_are_private_and_paginated(): void
    {
        [$service, $professional] = $this->service();
        $client = User::factory()->client()->create();
        $otherClient = User::factory()->client()->create();
        $owned = JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => $professional->id, 'service_id' => $service->id]);
        JobRequest::factory()->count(11)->create(['client_id' => $client->id, 'professional_id' => $professional->id, 'service_id' => $service->id]);
        $other = JobRequest::factory()->create(['client_id' => $otherClient->id, 'professional_id' => $professional->id, 'service_id' => $service->id]);

        $this->actingAs($client)->get(route('client.jobs.index'))->assertOk()->assertViewHas('jobs', fn ($jobs) => $jobs->perPage() === 10);
        $this->actingAs($professional->user)->get(route('professional.jobs.index'))->assertOk()->assertSee($other->title);
        $this->actingAs($professional->user)->get(route('job-requests.show', $owned))->assertOk()->assertSee($owned->title);
        $this->actingAs($client)->get(route('job-requests.show', $other))->assertForbidden();
        $this->actingAs(User::factory()->professional()->create())->get(route('job-requests.show', $owned))->assertForbidden();
    }

    public function test_quote_service_requires_a_price_and_acceptance_keeps_historical_price(): void
    {
        [$service, $professional] = $this->service(['price_type' => PriceType::QUOTE, 'price' => null]);
        $client = User::factory()->client()->create();
        $job = JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => $professional->id, 'service_id' => $service->id, 'agreed_price' => null]);

        $this->actingAs($professional->user)->post(route('job-quotes.store', $job), [
            'amount' => '850.50',
            'description' => 'Incluye mano de obra y materiales.',
        ])
            ->assertRedirect();
        $this->assertSame(JobStatus::ACCEPTED, $job->fresh()->status);
        $quote = $job->fresh()->quotes()->firstOrFail();
        $this->actingAs($client)->post(route('job-quotes.accept', $quote))->assertRedirect();
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->fresh()->status);
        $this->assertSame('850.50', $job->fresh()->agreed_price);

        $service->update(['price' => 1200]);
        $this->assertSame('850.50', $job->fresh()->agreed_price);
    }

    public function test_fixed_service_accepts_with_explicit_service_price(): void
    {
        [$service, $professional] = $this->service(['price' => 650, 'price_type' => PriceType::FIXED]);
        $client = User::factory()->client()->create();
        $job = JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => $professional->id, 'service_id' => $service->id, 'agreed_price' => null]);

        $this->actingAs($professional->user)->post(route('job-quotes.store', $job), [
            'amount' => '650',
            'description' => 'Incluye el precio publicado del servicio.',
        ])->assertRedirect();
        $quote = $job->fresh()->quotes()->firstOrFail();
        $client = User::factory()->client()->create();
        $job->update(['client_id' => $client->id]);
        $this->actingAs($client)->post(route('job-quotes.accept', $quote))->assertRedirect();
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->fresh()->status);
        $this->assertSame('650.00', $job->fresh()->agreed_price);
    }

    public function test_professional_can_reject_pending_request(): void
    {
        [$service, $professional] = $this->service();
        $job = JobRequest::factory()->create(['professional_id' => $professional->id, 'service_id' => $service->id]);

        $this->actingAs($professional->user)->post(route('job-requests.reject', $job))->assertRedirect();
        $this->assertSame(JobStatus::REJECTED, $job->fresh()->status);
        $this->actingAs($professional->user)->post(route('job-requests.start', $job))->assertForbidden();
    }

    public function test_complete_flow_requires_professional_then_client_confirmation(): void
    {
        [$service, $professional] = $this->service();
        $client = User::factory()->client()->create();
        $job = JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => $professional->id, 'service_id' => $service->id]);

        $quoteResponse = $this->actingAs($professional->user)->post(route('job-quotes.store', $job), ['amount' => 700, 'description' => 'Incluye mano de obra y materiales básicos.']);
        $quoteResponse->assertRedirect();
        $this->assertSame(JobStatus::ACCEPTED, $job->fresh()->status);
        $quote = $job->fresh()->quotes()->firstOrFail();
        $acceptResponse = $this->actingAs($client)->post(route('job-quotes.accept', $quote));
        $acceptResponse->assertRedirect();
        $this->assertSame(JobStatus::AWAITING_PAYMENT, $job->fresh()->status);
        $this->actingAs($professional->user)->post(route('job-requests.start', $job))->assertForbidden();
        $job->update(['status' => JobStatus::PAID]);
        $this->actingAs($professional->user)->post(route('job-requests.start', $job))->assertRedirect();
        $this->assertSame(JobStatus::IN_PROGRESS, $job->fresh()->status);
        $this->actingAs($professional->user)->post(route('job-requests.finish', $job))->assertRedirect();
        $this->assertSame(JobStatus::AWAITING_CONFIRMATION, $job->fresh()->status);
        $this->assertNotNull($job->fresh()->finished_at);
        $this->actingAs($professional->user)->post(route('job-requests.complete', $job))->assertSessionHasErrors('completion_code');
        $this->actingAs($client)->post(route('job-requests.complete', $job))->assertForbidden();
        $this->actingAs($professional->user)->post(route('job-requests.complete', $job), [
            'completion_code' => $job->fresh()->completion_code,
        ])->assertRedirect();
        $this->actingAs($client)->post(route('job-requests.complete', $job))->assertForbidden();
        $this->assertSame(JobStatus::COMPLETED, $job->fresh()->status);
        $this->assertNotNull($job->fresh()->completed_at);
    }

    public function test_invalid_transitions_and_manual_status_changes_are_blocked(): void
    {
        [$service, $professional] = $this->service();
        $client = User::factory()->client()->create();
        $job = JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => $professional->id, 'service_id' => $service->id, 'status' => JobStatus::PENDING]);

        $this->actingAs($professional->user)->post(route('job-requests.start', $job))->assertForbidden();
        $this->actingAs($professional->user)->post(route('job-requests.finish', $job))->assertForbidden();
        $job->update(['status' => JobStatus::COMPLETED]);
        $this->actingAs($professional->user)->post(route('job-requests.start', $job))->assertForbidden();
        $this->assertSame(JobStatus::COMPLETED, $job->fresh()->status);
    }

    public function test_client_and_professional_can_cancel_before_start_but_not_after(): void
    {
        [$service, $professional] = $this->service();
        $client = User::factory()->client()->create();
        $job = JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => $professional->id, 'service_id' => $service->id]);
        $this->actingAs($client)->post(route('job-requests.cancel', $job), ['cancellation_reason' => 'Ya no lo necesito'])->assertRedirect();
        $this->assertSame(JobStatus::CANCELLED, $job->fresh()->status);

        $job->update(['status' => JobStatus::IN_PROGRESS, 'cancelled_at' => null]);
        $this->actingAs($professional->user)->post(route('job-requests.cancel', $job))->assertForbidden();
        $this->assertSame(JobStatus::IN_PROGRESS, $job->fresh()->status);
    }

    private function service(array $attributes = []): array
    {
        $category = Category::factory()->create();
        $professional = ProfessionalProfile::factory()->create();
        $service = Service::factory()->create(array_merge([
            'professional_id' => $professional->id,
            'category_id' => $category->id,
        ], $attributes));

        return [$service, $professional];
    }
}
