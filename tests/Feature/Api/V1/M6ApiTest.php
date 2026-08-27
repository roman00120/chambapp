<?php

namespace Tests\Feature\Api\V1;

use App\Enums\JobDisputeStatus;
use App\Enums\JobStatus;
use App\Enums\PaymentStatus;
use App\Models\JobDispute;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class M6ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_paid_m6_workflow_uses_explicit_actions_and_keeps_payment_immutable(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->verifiedIdentity()->create();
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => JobStatus::PAID,
        ]);
        $payment = Payment::factory()->create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => PaymentStatus::APPROVED,
            'gross_amount' => '1000.00',
            'platform_fee_percent' => '15.00',
            'platform_fee' => '150.00',
            'professional_amount' => '850.00',
            'paid_at' => now(),
        ]);
        $snapshot = $payment->only([
            'gross_amount', 'platform_fee_percent', 'platform_fee',
            'provider_fee', 'professional_amount', 'status', 'refunded_at',
        ]);

        Sanctum::actingAs($professional->user);
        $this->postJson('/api/v1/jobs/'.$job->id.'/on-the-way')
            ->assertOk()->assertJsonPath('data.status', 'on_the_way');
        $this->postJson('/api/v1/jobs/'.$job->id.'/arrived')
            ->assertOk()->assertJsonPath('data.status', 'arrived');
        $this->postJson('/api/v1/jobs/'.$job->id.'/start')
            ->assertOk()->assertJsonPath('data.status', 'in_progress');
        $this->postJson('/api/v1/jobs/'.$job->id.'/finish')
            ->assertOk()->assertJsonPath('data.status', 'awaiting_confirmation')
            ->assertJsonMissingPath('data.completion_code');
        $this->getJson('/api/v1/jobs/'.$job->id)
            ->assertOk()->assertJsonMissingPath('data.completion_code');

        Sanctum::actingAs($client);
        $code = $this->getJson('/api/v1/jobs/'.$job->id)
            ->assertOk()
            ->assertJsonPath('data.status', 'awaiting_confirmation')
            ->json('data.completion_code');
        $this->postJson('/api/v1/jobs/'.$job->id.'/confirm', [
            'completion_code' => $code,
        ])->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonMissingPath('data.completion_code');

        $this->assertEquals($snapshot, $payment->fresh()->only(array_keys($snapshot)));
    }

    public function test_completion_code_is_only_exposed_to_owner_on_detail_while_valid_and_awaiting_confirmation(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        $job = $this->awaitingConfirmationJob($client, $professional);

        Sanctum::actingAs($client);
        $this->getJson('/api/v1/jobs/'.$job->id)
            ->assertOk()
            ->assertJsonPath('data.completion_code', '123456');
        $this->getJson('/api/v1/jobs')
            ->assertOk()
            ->assertJsonMissingPath('data.0.completion_code');

        Sanctum::actingAs($professional->user);
        $this->getJson('/api/v1/jobs/'.$job->id)
            ->assertOk()
            ->assertJsonMissingPath('data.completion_code');
        $this->getJson('/api/v1/professional/jobs')
            ->assertOk()
            ->assertJsonMissingPath('data.0.completion_code');

        $otherClient = User::factory()->client()->create();
        Sanctum::actingAs($otherClient);
        $this->getJson('/api/v1/jobs/'.$job->id)->assertForbidden();
    }

    public function test_completion_code_is_hidden_outside_valid_awaiting_confirmation_state(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();

        foreach ([JobStatus::PAID, JobStatus::COMPLETED, JobStatus::DISPUTED, JobStatus::CANCELLED] as $status) {
            $job = JobRequest::factory()->create([
                'client_id' => $client->id,
                'professional_id' => $professional->id,
                'status' => $status,
                'completion_code' => '123456',
                'completion_code_expires_at' => now()->addHour(),
            ]);
            Sanctum::actingAs($client);
            $this->getJson('/api/v1/jobs/'.$job->id)
                ->assertOk()
                ->assertJsonMissingPath('data.completion_code');
        }

        $expired = $this->awaitingConfirmationJob($client, $professional, now()->subMinute());
        Sanctum::actingAs($client);
        $this->getJson('/api/v1/jobs/'.$expired->id)
            ->assertOk()
            ->assertJsonMissingPath('data.completion_code');
    }

    public function test_client_can_confirm_with_code_obtained_from_detail_and_invalid_code_is_rejected(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        $job = $this->awaitingConfirmationJob($client, $professional);

        Sanctum::actingAs($client);
        $code = $this->getJson('/api/v1/jobs/'.$job->id)
            ->assertOk()
            ->json('data.completion_code');

        $this->postJson('/api/v1/jobs/'.$job->id.'/confirm', ['completion_code' => '999999'])
            ->assertConflict()
            ->assertJsonPath('code', 'INVALID_JOB_TRANSITION');
        $this->postJson('/api/v1/jobs/'.$job->id.'/confirm', ['completion_code' => $code])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonMissingPath('data.completion_code');
    }

    public function test_dispute_requires_authentication_client_role_and_job_ownership(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        $job = $this->awaitingConfirmationJob($client, $professional);
        $payload = ['reason' => 'incomplete_work'];

        $this->postJson('/api/v1/jobs/'.$job->id.'/dispute', $payload)->assertUnauthorized();

        Sanctum::actingAs($professional->user);
        $this->postJson('/api/v1/jobs/'.$job->id.'/dispute', $payload)->assertForbidden();

        Sanctum::actingAs(User::factory()->client()->create());
        $this->postJson('/api/v1/jobs/'.$job->id.'/dispute', $payload)->assertForbidden();
    }

    public function test_owner_can_open_dispute_without_changing_payment_or_refunding(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        $job = $this->awaitingConfirmationJob($client, $professional);
        $payment = Payment::factory()->create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => PaymentStatus::APPROVED,
            'gross_amount' => '900.00',
            'platform_fee_percent' => '10.00',
            'platform_fee' => '90.00',
            'provider_fee' => '12.34',
            'professional_amount' => '797.66',
            'paid_at' => now()->subHour(),
            'refunded_at' => null,
        ]);
        $financialSnapshot = $payment->only([
            'gross_amount',
            'platform_fee_percent',
            'platform_fee',
            'provider_fee',
            'professional_amount',
            'status',
            'paid_at',
            'refunded_at',
        ]);

        Sanctum::actingAs($client);
        $this->postJson('/api/v1/jobs/'.$job->id.'/dispute', [
            'reason' => 'not_as_agreed',
            'description' => 'El alcance entregado no coincide con lo acordado.',
            'client_id' => User::factory()->client()->create()->id,
        ])->assertOk()
            ->assertJsonPath('data.status', 'disputed')
            ->assertJsonPath('message', 'Tu reporte fue enviado y será revisado.')
            ->assertJsonMissingPath('data.dispute')
            ->assertJsonMissingPath('data.completion_code');

        $this->assertDatabaseHas('job_disputes', [
            'job_request_id' => $job->id,
            'opened_by' => $client->id,
            'reason' => 'not_as_agreed',
            'status' => JobDisputeStatus::OPEN->value,
        ]);
        $this->assertSame(JobStatus::DISPUTED, $job->fresh()->status);
        $this->assertEquals($financialSnapshot, $payment->fresh()->only(array_keys($financialSnapshot)));
        $this->assertNull($payment->fresh()->refunded_at);
    }

    public function test_dispute_validates_payload_state_and_active_duplicate(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        $job = $this->awaitingConfirmationJob($client, $professional);
        Sanctum::actingAs($client);

        $this->postJson('/api/v1/jobs/'.$job->id.'/dispute', ['reason' => 'invented'])
            ->assertUnprocessable();
        $this->postJson('/api/v1/jobs/'.$job->id.'/dispute', [
            'reason' => 'other',
            'description' => str_repeat('a', 1001),
        ])->assertUnprocessable();

        $paid = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => JobStatus::PAID,
        ]);
        $this->postJson('/api/v1/jobs/'.$paid->id.'/dispute', ['reason' => 'other'])
            ->assertForbidden();

        JobDispute::factory()->create([
            'job_request_id' => $job->id,
            'opened_by' => $client->id,
            'status' => JobDisputeStatus::OPEN,
        ]);
        $this->postJson('/api/v1/jobs/'.$job->id.'/dispute', ['reason' => 'other'])
            ->assertForbidden();
        $this->assertSame(1, $job->dispute()->count());
    }

    public function test_existing_web_dispute_route_still_works(): void
    {
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        $job = $this->awaitingConfirmationJob($client, $professional);

        $this->actingAs($client)
            ->post(route('job-requests.dispute', $job), ['reason' => 'professional_absent'])
            ->assertRedirect(route('job-requests.show', $job))
            ->assertSessionHas('status');
        $this->assertSame(JobStatus::DISPUTED, $job->fresh()->status);
    }

    private function awaitingConfirmationJob(
        User $client,
        ProfessionalProfile $professional,
        mixed $expiresAt = null,
    ): JobRequest {
        return JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'status' => JobStatus::AWAITING_CONFIRMATION,
            'completion_code' => '123456',
            'completion_code_expires_at' => $expiresAt ?? now()->addHour(),
            'finished_at' => now(),
        ]);
    }
}
