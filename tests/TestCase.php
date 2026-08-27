<?php

namespace Tests;

use App\Enums\IdentityVerificationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\ProfessionalIdentityVerification;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Helper to create an identity-verified professional ready for commercial operations.
     */
    protected function createVerifiedProfessional(array $userAttributes = [], array $profileAttributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => UserRole::PROFESSIONAL,
            'status' => UserStatus::ACTIVE,
        ], $userAttributes));

        $profile = ProfessionalProfile::factory()->create(array_merge([
            'user_id' => $user->id,
            'is_available' => true,
        ], $profileAttributes));

        $this->verifyProfessionalIdentity($profile);

        return $user->fresh(['professionalProfile.identityVerification']);
    }

    /**
     * Helper to explicitly verify a professional profile or user identity.
     */
    protected function verifyProfessionalIdentity(ProfessionalProfile|User $professional): ProfessionalIdentityVerification
    {
        $profile = $professional instanceof User ? $professional->professionalProfile : $professional;

        if (! $profile) {
            $profile = ProfessionalProfile::factory()->create([
                'user_id' => $professional->id,
            ]);
        }

        return ProfessionalIdentityVerification::updateOrCreate(
            ['professional_id' => $profile->id],
            [
                'verification_provider' => 'didit',
                'provider_verification_id' => (string) \Illuminate\Support\Str::uuid(),
                'provider_session_id' => (string) \Illuminate\Support\Str::uuid(),
                'status' => IdentityVerificationStatus::VERIFIED,
                'provider_status' => 'approved',
                'started_at' => now(),
                'submitted_at' => now(),
                'verified_at' => now(),
                'expires_at' => null,
            ]
        );
    }
}
