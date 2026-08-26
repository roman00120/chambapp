<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'chambapp.legal.registration_acceptance_required' => true,
            'chambapp.legal.documents_final' => true,
            'chambapp.legal.professional_terms_enabled' => false,
            'chambapp.legal.documents.terms.version' => '2026-08-26',
            'chambapp.legal.documents.privacy.version' => '2026-08-26',
        ]);
    }

    public function test_web_registration_rejects_missing_acceptance_and_records_valid_server_evidence(): void
    {
        $payload = $this->webPayload('client');
        unset($payload['legal_accepted']);
        $this->post(route('register.store'), $payload)->assertSessionHasErrors('legal_accepted');
        $this->assertDatabaseCount('users', 0);

        $before = now()->startOfSecond();
        $this->post(route('register.store'), $this->webPayload('client'))
            ->assertRedirect(route('client.dashboard'));
        $user = User::query()->firstOrFail();
        $this->assertDatabaseCount('legal_acceptances', 2);
        $this->assertTrue($user->legalAcceptances()->get()->every(
            fn ($acceptance): bool => $acceptance->platform === 'web'
                && $acceptance->accepted_at->gte($before)
                && strlen((string) $acceptance->ip_hash) === 64
                && ($acceptance->user_agent_hash === null || strlen((string) $acceptance->user_agent_hash) === 64),
        ));
    }

    public function test_api_cannot_bypass_acceptance_or_submit_stale_or_extra_documents(): void
    {
        $payload = $this->apiPayload('client');
        unset($payload['legal_accepted']);
        $this->postJson('/api/v1/auth/register', $payload)->assertUnprocessable()->assertJsonValidationErrors('legal_accepted');

        $payload = $this->apiPayload('client');
        $payload['legal_documents']['terms'] = 'stale-version';
        $this->postJson('/api/v1/auth/register', $payload)->assertUnprocessable()->assertJsonValidationErrors('legal_documents');

        $payload = $this->apiPayload('client');
        $payload['legal_documents']['professional_terms'] = 'invented';
        $this->postJson('/api/v1/auth/register', $payload)->assertUnprocessable()->assertJsonValidationErrors('legal_documents');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_client_and_professional_record_only_documents_currently_applicable_to_their_role(): void
    {
        $client = $this->apiPayload('client');
        $this->withHeader('X-Chambapp-Platform', 'flutter')->postJson('/api/v1/auth/register', $client)->assertCreated();
        $clientUser = User::query()->where('email', $client['email'])->firstOrFail();
        $this->assertEqualsCanonicalizing(['privacy', 'terms'], $clientUser->legalAcceptances()->pluck('document_type')->all());
        $this->assertTrue($clientUser->legalAcceptances()->get()->every(fn ($item): bool => $item->platform === 'flutter'));

        $professional = $this->apiPayload('professional', 'professional@example.test');
        $this->postJson('/api/v1/auth/register', $professional)->assertCreated();
        $professionalUser = User::query()->where('email', $professional['email'])->firstOrFail();
        $this->assertEqualsCanonicalizing(['privacy', 'terms'], $professionalUser->legalAcceptances()->pluck('document_type')->all());
        $this->assertDatabaseHas('professional_identity_verifications', [
            'professional_id' => $professionalUser->professionalProfile->id,
            'status' => 'not_started',
        ]);
    }

    public function test_payload_cannot_assign_acceptance_to_another_user_and_existing_users_are_not_backfilled(): void
    {
        $existing = User::factory()->client()->create();
        $payload = $this->apiPayload('client');
        $payload['user_id'] = $existing->id;
        $this->postJson('/api/v1/auth/register', $payload)->assertCreated();

        $this->assertSame(0, $existing->legalAcceptances()->count());
        $created = User::query()->where('email', $payload['email'])->firstOrFail();
        $this->assertSame(2, $created->legalAcceptances()->count());
    }

    public function test_registration_requirements_publish_exact_current_versions_and_drafts_stop_registration(): void
    {
        $this->getJson('/api/v1/auth/registration-requirements?role=professional')
            ->assertOk()
            ->assertJsonPath('data.acceptance_required', true)
            ->assertJsonPath('data.registration_available', true)
            ->assertJsonCount(2, 'data.documents')
            ->assertJsonMissing(['document' => 'professional_terms']);

        config(['chambapp.legal.documents_final' => false]);
        $this->postJson('/api/v1/auth/register', $this->apiPayload('client'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('legal_accepted');
        $this->assertDatabaseCount('users', 0);
    }

    private function webPayload(string $role): array
    {
        return [
            'name' => 'Persona Legal',
            'email' => 'legal-web@example.test',
            'phone' => '5512345678',
            'account_type' => $role,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'legal_accepted' => '1',
            'legal_documents' => ['terms' => '2026-08-26', 'privacy' => '2026-08-26'],
        ];
    }

    private function apiPayload(string $role, string $email = 'legal-api@example.test'): array
    {
        return [
            'name' => 'Persona API Legal',
            'email' => $email,
            'phone' => '5512345678',
            'role' => $role,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'device_name' => 'Legal test',
            'legal_accepted' => true,
            'legal_documents' => ['terms' => '2026-08-26', 'privacy' => '2026-08-26'],
        ];
    }
}
