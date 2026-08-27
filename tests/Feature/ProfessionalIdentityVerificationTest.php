<?php

namespace Tests\Feature;

use App\Enums\IdentityVerificationStatus;
use App\Enums\VerificationStatus;
use App\Exceptions\IdentityVerificationRequiredException;
use App\Models\Category;
use App\Models\ProfessionalProfile;
use App\Models\User;
use App\Services\ProfessionalIdentityVerificationService;
use App\Services\UserRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfessionalIdentityVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_professional_without_record_has_safe_fallback_and_feature_flag_is_backward_compatible(): void
    {
        config()->set('chambapp.identity_verification.required', false);
        $profile = ProfessionalProfile::factory()->create();
        $service = app(ProfessionalIdentityVerificationService::class);

        $this->assertSame(IdentityVerificationStatus::NOT_STARTED, $service->statusFor($profile));
        $this->assertTrue($service->professionalCanAcceptJobs($profile));
        $this->assertFalse($profile->hasVerifiedIdentity());
    }

    public function test_new_professional_starts_not_started(): void
    {
        $user = app(UserRegistrationService::class)->register([
            'name' => 'Nueva Profesional',
            'email' => 'new-professional@example.test',
            'phone' => '3312345678',
            'password' => 'Password123!',
            'account_type' => 'professional',
        ]);

        $this->assertDatabaseHas('professional_identity_verifications', [
            'professional_id' => $user->professionalProfile->id,
            'status' => IdentityVerificationStatus::NOT_STARTED->value,
        ]);
    }

    public function test_required_feature_blocks_every_non_verified_status_and_accepts_current_verified_status(): void
    {
        config()->set('chambapp.identity_verification.required', true);
        $profile = ProfessionalProfile::factory()->create();
        $service = app(ProfessionalIdentityVerificationService::class);

        foreach ([
            IdentityVerificationStatus::NOT_STARTED,
            IdentityVerificationStatus::PENDING,
            IdentityVerificationStatus::REJECTED,
            IdentityVerificationStatus::NEEDS_REVIEW,
            IdentityVerificationStatus::EXPIRED,
        ] as $status) {
            $profile->identityVerification()->updateOrCreate([], ['status' => $status]);
            $profile->unsetRelation('identityVerification');
            $this->assertFalse($service->professionalCanAcceptJobs($profile), $status->value);
        }

        $profile->identityVerification()->updateOrCreate([], [
            'status' => IdentityVerificationStatus::VERIFIED,
            'verified_at' => now(),
            'expires_at' => now()->addYear(),
        ]);
        $profile->unsetRelation('identityVerification');

        $this->assertTrue($service->professionalCanAcceptJobs($profile));
        $service->ensureProfessionalCanAcceptJobs($profile);

        $profile->identityVerification()->update(['expires_at' => now()->subSecond()]);
        $profile->unsetRelation('identityVerification');
        $this->expectException(IdentityVerificationRequiredException::class);
        $service->ensureProfessionalCanAcceptJobs($profile);
    }

    public function test_own_status_api_is_read_only_and_does_not_expose_provider_reference(): void
    {
        $profile = ProfessionalProfile::factory()->create();
        $profile->identityVerification()->create([
            'status' => IdentityVerificationStatus::PENDING,
            'provider_verification_id' => 'private-provider-reference',
            'submitted_at' => now(),
        ]);
        Sanctum::actingAs($profile->user);

        $this->getJson('/api/v1/professional/identity-verification')
            ->assertOk()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.identity_verified', false)
            ->assertJsonPath('data.documents_stored_by_chambapp', false)
            ->assertJsonMissing(['provider_verification_id' => 'private-provider-reference']);

        $this->postJson('/api/v1/professional/identity-verification', ['status' => 'verified'])
            ->assertMethodNotAllowed();
        $this->assertDatabaseHas('professional_identity_verifications', [
            'professional_id' => $profile->id,
            'status' => IdentityVerificationStatus::PENDING->value,
        ]);
    }

    public function test_required_verification_cannot_be_bypassed_through_availability_api(): void
    {
        config()->set('chambapp.identity_verification.required', true);
        $profile = ProfessionalProfile::factory()->create();
        $profile->identityVerification()->create(['status' => IdentityVerificationStatus::PENDING]);
        Sanctum::actingAs($profile->user);

        $this->putJson('/api/v1/professional/availability', [
            'is_available' => true,
            'service_radius_km' => 10,
            'latitude' => 20.67,
            'longitude' => -103.35,
        ])->assertForbidden()->assertJsonPath('code', 'IDENTITY_VERIFICATION_REQUIRED');

        $this->assertFalse($profile->fresh()->is_available);
    }

    public function test_required_verification_cannot_be_bypassed_when_creating_services_on_web_or_api(): void
    {
        config()->set('chambapp.identity_verification.required', true);
        $profile = ProfessionalProfile::factory()->create();
        $profile->identityVerification()->create(['status' => IdentityVerificationStatus::PENDING]);
        $category = Category::factory()->create(['is_active' => true]);
        $payload = [
            'category_id' => $category->id,
            'title' => 'Servicio bloqueado por identidad',
            'description' => 'Una descripción suficientemente amplia para validar el servicio.',
            'price_type' => 'fixed',
            'price' => '500.00',
        ];

        $this->actingAs($profile->user)
            ->from(route('professional.services.create'))
            ->post(route('professional.services.store'), $payload)
            ->assertRedirect(route('professional.identity-verification.show'));

        Sanctum::actingAs($profile->user);
        $this->postJson('/api/v1/professional/services', $payload)
            ->assertForbidden()
            ->assertJsonPath('code', 'IDENTITY_VERIFICATION_REQUIRED');

        $this->assertDatabaseCount('services', 0);
    }

    public function test_public_api_badge_uses_only_real_identity_status(): void
    {
        $profile = ProfessionalProfile::factory()->create();

        $this->getJson('/api/v1/professionals/'.$profile->id)
            ->assertOk()
            ->assertJsonPath('data.identity_verified', false)
            ->assertJsonPath('data.verified', false);

        $profile->identityVerification()->create([
            'status' => IdentityVerificationStatus::VERIFIED,
            'verified_at' => now(),
        ]);

        $this->getJson('/api/v1/professionals/'.$profile->id)
            ->assertOk()
            ->assertJsonPath('data.identity_verified', true)
            ->assertJsonPath('data.verified', true);
    }

    public function test_admin_profile_approval_never_marks_identity_verified(): void
    {
        $admin = User::factory()->admin()->create();
        $profile = ProfessionalProfile::factory()->unverified()->create();

        $this->actingAs($admin)->post(route('admin.professionals.approve', $profile))->assertRedirect();

        $this->assertSame(VerificationStatus::VERIFIED, $profile->fresh()->verification_status);
        $this->assertFalse($profile->fresh()->hasVerifiedIdentity());
        $this->assertDatabaseMissing('professional_identity_verifications', [
            'professional_id' => $profile->id,
            'status' => IdentityVerificationStatus::VERIFIED->value,
        ]);
    }

    public function test_operational_query_excludes_unverified_profiles_when_enabled(): void
    {
        config()->set('chambapp.identity_verification.required', true);
        $verified = ProfessionalProfile::factory()->create();
        $pending = ProfessionalProfile::factory()->create();
        $verified->identityVerification()->create(['status' => IdentityVerificationStatus::VERIFIED, 'verified_at' => now()]);
        $pending->identityVerification()->create(['status' => IdentityVerificationStatus::PENDING]);

        $eligible = app(ProfessionalIdentityVerificationService::class)
            ->applyOperationalEligibility(ProfessionalProfile::query())
            ->pluck('id');

        $this->assertTrue($eligible->contains($verified->id));
        $this->assertFalse($eligible->contains($pending->id));
    }
}
