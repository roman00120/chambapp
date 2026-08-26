<?php

namespace Tests\Feature;

use App\Enums\IdentityVerificationStatus;
use App\Exceptions\DiditException;
use App\Jobs\ProcessDiditWebhook;
use App\Models\DiditWebhookEvent;
use App\Models\ProfessionalProfile;
use App\Models\User;
use App\Services\DiditIdentityVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DiditIdentityVerificationTest extends TestCase
{
    use RefreshDatabase;

    private const WORKFLOW_ID = '11111111-2222-3333-4444-555555555555';

    private const SESSION_ID = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('k', 32)),
            'chambapp.identity_verification.provider' => 'didit',
            'chambapp.identity_verification.required' => false,
            'chambapp.identity_verification.consent_version' => 'draft-test',
            'chambapp.identity_verification.privacy_notice_version' => 'draft-test',
            'services.didit.api_url' => 'https://verification.didit.me',
            'services.didit.api_key' => 'didit-api-test-key',
            'services.didit.workflow_id' => self::WORKFLOW_ID,
            'services.didit.webhook_secret' => 'didit-webhook-test-secret',
            'services.didit.timeout' => 2,
        ]);
    }

    public function test_professional_can_start_hosted_session_only_after_explicit_consent(): void
    {
        $profile = ProfessionalProfile::factory()->create();
        Sanctum::actingAs($profile->user);
        Http::fake([
            'https://verification.didit.me/v3/session/' => Http::response($this->sessionResponse(), 201),
        ]);

        $this->postJson('/api/v1/professional/identity-verification/start')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('identity_consent');

        $this->postJson('/api/v1/professional/identity-verification/start', ['identity_consent' => true])
            ->assertCreated()
            ->assertJsonPath('data.verification_url', 'https://verify.didit.me/session/test-token')
            ->assertJsonPath('data.status', 'pending');

        $record = $profile->identityVerification()->firstOrFail();
        $this->assertSame(self::SESSION_ID, $record->provider_session_id);
        $this->assertSame(IdentityVerificationStatus::PENDING, $record->status);
        $this->assertDatabaseHas('professional_identity_verification_consents', [
            'professional_id' => $profile->id,
            'identity_verification_id' => $record->id,
            'purpose' => DiditIdentityVerificationService::PURPOSE,
        ]);

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return $request->hasHeader('x-api-key', 'didit-api-test-key')
                && $data['workflow_id'] === self::WORKFLOW_ID
                && str_starts_with($data['vendor_data'], 'chambapp-professional-')
                && ! str_ends_with($data['vendor_data'], '-1')
                && $data['language'] === 'es'
                && $data['callback_method'] === 'both';
        });
    }

    public function test_unauthenticated_and_non_professional_users_cannot_start(): void
    {
        $this->postJson('/api/v1/professional/identity-verification/start', ['identity_consent' => true])
            ->assertUnauthorized();

        Sanctum::actingAs(User::factory()->client()->create());
        $this->postJson('/api/v1/professional/identity-verification/start', ['identity_consent' => true])
            ->assertForbidden();
    }

    public function test_missing_configuration_and_provider_fail_safely(): void
    {
        $profile = ProfessionalProfile::factory()->create();
        Sanctum::actingAs($profile->user);
        config(['services.didit.api_key' => null]);

        $this->getJson('/api/v1/professional/identity-verification')
            ->assertOk()
            ->assertJsonPath('data.can_start_verification', false);

        $this->postJson('/api/v1/professional/identity-verification/start', ['identity_consent' => true])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'DIDIT_NOT_CONFIGURED');
    }

    public function test_didit_http_errors_are_mapped_without_leaking_provider_payloads(): void
    {
        $sequence = Http::sequence();
        foreach ([400, 401, 429, 500] as $status) {
            $sequence->push(['detail' => 'provider-private-detail'], $status);
        }
        Http::fake(['https://verification.didit.me/v3/session/' => $sequence]);

        foreach ([400, 401, 429, 500] as $status) {
            $profile = ProfessionalProfile::factory()->create();
            Sanctum::actingAs($profile->user);

            $response = $this->postJson('/api/v1/professional/identity-verification/start', ['identity_consent' => true]);
            $response->assertStatus(in_array($status, [429, 500], true) ? 503 : 422)
                ->assertJsonMissing(['detail' => 'provider-private-detail']);
        }
    }

    public function test_timeout_is_reported_as_retryable_safe_error(): void
    {
        $profile = ProfessionalProfile::factory()->create();
        Sanctum::actingAs($profile->user);
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->postJson('/api/v1/professional/identity-verification/start', ['identity_consent' => true])
            ->assertServiceUnavailable()
            ->assertJsonPath('code', 'DIDIT_CONNECTION_FAILED');
    }

    public function test_repeated_start_uses_stable_vendor_data_and_one_active_record(): void
    {
        $profile = ProfessionalProfile::factory()->create();
        Sanctum::actingAs($profile->user);
        Http::fake([
            'https://verification.didit.me/v3/session/' => Http::response($this->sessionResponse(), 201),
        ]);

        $this->postJson('/api/v1/professional/identity-verification/start', ['identity_consent' => true])->assertCreated();
        $this->postJson('/api/v1/professional/identity-verification/start', ['identity_consent' => true])->assertCreated();

        $this->assertSame(1, $profile->identityVerification()->count());
        $this->assertSame(2, $profile->identityVerification()->firstOrFail()->consents()->count());
        $vendorData = [];
        Http::assertSent(function ($request) use (&$vendorData): bool {
            $vendorData[] = $request->data()['vendor_data'];

            return true;
        });
        $this->assertCount(2, $vendorData);
        $this->assertSame($vendorData[0], $vendorData[1]);
    }

    public function test_new_session_clears_expired_verification_dates(): void
    {
        $profile = ProfessionalProfile::factory()->create();
        $profile->identityVerification()->create([
            'verification_provider' => 'didit',
            'provider_session_id' => 'expired-session',
            'status' => IdentityVerificationStatus::EXPIRED,
            'verified_at' => now()->subYear(),
            'expires_at' => now()->subDay(),
        ]);
        Sanctum::actingAs($profile->user);
        Http::fake([
            'https://verification.didit.me/v3/session/' => Http::response($this->sessionResponse(), 201),
        ]);

        $this->postJson('/api/v1/professional/identity-verification/start', ['identity_consent' => true])
            ->assertCreated();

        $record = $profile->identityVerification()->firstOrFail();
        $this->assertSame(IdentityVerificationStatus::PENDING, $record->status);
        $this->assertNull($record->verified_at);
        $this->assertNull($record->expires_at);
    }

    public function test_decision_statuses_map_to_safe_chambapp_states(): void
    {
        $mapping = [
            'Approved' => IdentityVerificationStatus::VERIFIED,
            'Declined' => IdentityVerificationStatus::REJECTED,
            'In Review' => IdentityVerificationStatus::NEEDS_REVIEW,
            'Expired' => IdentityVerificationStatus::EXPIRED,
            'Kyc Expired' => IdentityVerificationStatus::EXPIRED,
            'Abandoned' => IdentityVerificationStatus::REJECTED,
            'Awaiting User' => IdentityVerificationStatus::PENDING,
            'Resubmitted' => IdentityVerificationStatus::PENDING,
        ];

        $cases = [];
        $sequence = Http::sequence();
        foreach ($mapping as $providerStatus => $expectedStatus) {
            $profile = ProfessionalProfile::factory()->create();
            $record = $profile->identityVerification()->create([
                'verification_provider' => 'didit',
                'provider_session_id' => self::SESSION_ID.'-'.$profile->id,
                'status' => IdentityVerificationStatus::PENDING,
            ]);
            $decision = $this->decisionResponse($profile, $record->provider_session_id, $providerStatus);
            $sequence->push($decision);
            $cases[] = [$profile, $record, $providerStatus, $expectedStatus];
        }
        Http::fake(['*' => $sequence]);

        foreach ($cases as [$profile, $record, $providerStatus, $expectedStatus]) {
            app(DiditIdentityVerificationService::class)->sync($profile);

            $this->assertSame($expectedStatus, $record->fresh()->status, $providerStatus);
        }
    }

    public function test_decision_rejects_wrong_session_vendor_and_workflow(): void
    {
        $cases = [];
        $sequence = Http::sequence();
        foreach (['session', 'kind', 'vendor', 'workflow'] as $mismatch) {
            $profile = ProfessionalProfile::factory()->create();
            $record = $profile->identityVerification()->create([
                'verification_provider' => 'didit',
                'provider_session_id' => self::SESSION_ID.'-'.$profile->id,
                'status' => IdentityVerificationStatus::PENDING,
            ]);
            $decision = $this->decisionResponse($profile, $record->provider_session_id, 'Approved');
            if ($mismatch === 'session') {
                $decision['session_id'] = 'wrong-session';
            } elseif ($mismatch === 'kind') {
                $decision['session_kind'] = 'workflow';
            } elseif ($mismatch === 'vendor') {
                $decision['vendor_data'] = 'wrong-vendor';
            } else {
                $decision['workflow_id'] = 'wrong-workflow';
            }
            $sequence->push($decision);
            $cases[] = [$profile, $record];
        }
        Http::fake(['*' => $sequence]);

        foreach ($cases as [$profile, $record]) {
            try {
                app(DiditIdentityVerificationService::class)->sync($profile);
                $this->fail('Expected a Didit mismatch exception.');
            } catch (DiditException) {
                $this->assertSame(IdentityVerificationStatus::PENDING, $record->fresh()->status);
            }
        }
    }

    public function test_browser_callback_cannot_mark_verified_from_query_parameters(): void
    {
        $profile = ProfessionalProfile::factory()->create();
        $record = $profile->identityVerification()->create([
            'verification_provider' => 'didit',
            'provider_session_id' => self::SESSION_ID,
            'status' => IdentityVerificationStatus::PENDING,
        ]);

        $this->actingAs($profile->user)
            ->get(route('professional.identity-verification.callback', [
                'verificationSessionId' => 'attacker-session',
                'status' => 'Approved',
            ]))
            ->assertNotFound();

        $this->assertSame(IdentityVerificationStatus::PENDING, $record->fresh()->status);
    }

    public function test_valid_browser_callback_refetches_decision_before_verifying(): void
    {
        $profile = ProfessionalProfile::factory()->create();
        $record = $profile->identityVerification()->create([
            'verification_provider' => 'didit',
            'provider_session_id' => self::SESSION_ID,
            'status' => IdentityVerificationStatus::PENDING,
        ]);
        Http::fake(['*' => Http::response($this->decisionResponse($profile, self::SESSION_ID, 'Approved'))]);

        $this->actingAs($profile->user)
            ->get(route('professional.identity-verification.callback', [
                'verificationSessionId' => self::SESSION_ID,
                'status' => 'Declined',
            ]))
            ->assertRedirect(route('professional.identity-verification.show'));

        $this->assertSame(IdentityVerificationStatus::VERIFIED, $record->fresh()->status);
    }

    public function test_start_endpoint_is_rate_limited(): void
    {
        $profile = ProfessionalProfile::factory()->create();
        Sanctum::actingAs($profile->user);
        Http::fake([
            'https://verification.didit.me/v3/session/' => Http::response($this->sessionResponse(), 201),
        ]);

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->postJson('/api/v1/professional/identity-verification/start', ['identity_consent' => true])
                ->assertCreated();
        }
        $this->postJson('/api/v1/professional/identity-verification/start', ['identity_consent' => true])
            ->assertTooManyRequests();
    }

    public function test_valid_webhook_is_accepted_once_and_invalid_or_replayed_signatures_are_rejected(): void
    {
        Bus::fake();
        $now = time();
        $payload = [
            'event_id' => 'event-123',
            'webhook_type' => 'status.updated',
            'timestamp' => $now,
            'session_id' => self::SESSION_ID,
            'status' => 'Approved',
        ];
        $raw = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = (string) $now;
        $signature = hash_hmac('sha256', $raw, 'didit-webhook-test-secret');

        $this->callWebhook($raw, $timestamp, $signature)->assertAccepted();
        $this->callWebhook($raw, $timestamp, $signature)
            ->assertOk()
            ->assertJsonPath('duplicate', true);
        $this->assertDatabaseCount('didit_webhook_events', 1);
        Bus::assertDispatchedTimes(ProcessDiditWebhook::class, 1);

        $invalidPayload = str_replace('event-123', 'event-invalid', $raw);
        $this->callWebhook($invalidPayload, $timestamp, 'invalid')->assertUnauthorized();
        $this->callWebhook($invalidPayload, (string) (time() - 301), hash_hmac('sha256', $invalidPayload, 'didit-webhook-test-secret'))
            ->assertUnauthorized();
    }

    public function test_webhook_job_refetches_decision_and_is_idempotent(): void
    {
        $profile = ProfessionalProfile::factory()->create();
        $record = $profile->identityVerification()->create([
            'verification_provider' => 'didit',
            'provider_session_id' => self::SESSION_ID,
            'status' => IdentityVerificationStatus::PENDING,
        ]);
        $event = DiditWebhookEvent::query()->create([
            'event_id' => 'event-job-1',
            'webhook_type' => 'status.updated',
            'provider_session_id' => self::SESSION_ID,
            'payload_hash' => hash('sha256', 'payload-without-pii'),
            'processing_status' => 'received',
            'received_at' => now(),
        ]);
        Http::fake(['*' => Http::response($this->decisionResponse($profile, self::SESSION_ID, 'Approved'))]);

        $job = new ProcessDiditWebhook($event->id);
        $job->handle(app(DiditIdentityVerificationService::class));
        $job->handle(app(DiditIdentityVerificationService::class));

        $this->assertSame(IdentityVerificationStatus::VERIFIED, $record->fresh()->status);
        $this->assertSame('processed', $event->fresh()->processing_status);
        Http::assertSentCount(1);
        $this->assertDatabaseCount('professional_identity_verification_events', 1);
    }

    /** @return array<string, mixed> */
    private function sessionResponse(): array
    {
        return [
            'session_id' => self::SESSION_ID,
            'url' => 'https://verify.didit.me/session/test-token',
            'status' => 'Not Started',
            'workflow_id' => self::WORKFLOW_ID,
            'vendor_data' => 'echoed-by-provider',
        ];
    }

    /** @return array<string, mixed> */
    private function decisionResponse(ProfessionalProfile $profile, string $sessionId, string $status): array
    {
        return [
            'session_id' => $sessionId,
            'session_kind' => 'user',
            'status' => $status,
            'workflow_id' => self::WORKFLOW_ID,
            'vendor_data' => app(DiditIdentityVerificationService::class)->vendorDataFor($profile),
            'expires_at' => now()->addYear()->toIso8601String(),
            'id_verifications' => [['status' => $status]],
            'liveness_checks' => [['status' => $status]],
            'face_matches' => [['status' => $status]],
        ];
    }

    private function callWebhook(string $raw, string $timestamp, string $signature): TestResponse
    {
        return $this->call('POST', '/webhooks/didit', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_TIMESTAMP' => $timestamp,
            'HTTP_X_SIGNATURE' => $signature,
        ], $raw);
    }
}
