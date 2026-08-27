<?php

namespace Database\Factories;

use App\Enums\IdentityVerificationStatus;
use App\Enums\VerificationStatus;
use App\Models\ProfessionalIdentityVerification;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProfessionalProfile> */
class ProfessionalProfileFactory extends Factory
{
    protected $model = ProfessionalProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => fn () => User::factory()->professional(),
            'bio' => fake()->paragraph(),
            'experience_years' => fake()->numberBetween(1, 20),
            'city' => fake()->randomElement(['Ciudad de México', 'Guadalajara', 'Monterrey', 'Puebla', 'Mérida']),
            'state' => fake()->randomElement(['Ciudad de México', 'Jalisco', 'Nuevo León', 'Puebla', 'Yucatán']),
            'postal_code' => fake()->numerify('#####'),
            'latitude' => fake()->randomFloat(7, 16, 30),
            'longitude' => fake()->randomFloat(7, -110, -86),
            'verification_status' => VerificationStatus::VERIFIED,
            'profile_photo' => null,
            'average_rating' => '0.00',
            'total_reviews' => 0,
            'total_completed_jobs' => 0,
        ];
    }

    public function unverified(): static
    {
        return $this->state(['verification_status' => VerificationStatus::UNVERIFIED]);
    }

    public function pendingVerification(): static
    {
        return $this->state(['verification_status' => VerificationStatus::PENDING]);
    }

    public function verifiedIdentity(): static
    {
        return $this->afterCreating(function (ProfessionalProfile $profile) {
            ProfessionalIdentityVerification::updateOrCreate(
                ['professional_id' => $profile->id],
                [
                    'verification_provider' => 'didit',
                    'provider_verification_id' => fake()->uuid(),
                    'provider_session_id' => fake()->uuid(),
                    'status' => IdentityVerificationStatus::VERIFIED,
                    'provider_status' => 'approved',
                    'started_at' => now(),
                    'submitted_at' => now(),
                    'verified_at' => now(),
                    'expires_at' => null,
                ]
            );
        });
    }

    public function pendingIdentity(): static
    {
        return $this->afterCreating(function (ProfessionalProfile $profile) {
            ProfessionalIdentityVerification::updateOrCreate(
                ['professional_id' => $profile->id],
                [
                    'verification_provider' => 'didit',
                    'provider_verification_id' => fake()->uuid(),
                    'provider_session_id' => fake()->uuid(),
                    'status' => IdentityVerificationStatus::PENDING,
                    'provider_status' => 'pending',
                    'started_at' => now(),
                    'submitted_at' => now(),
                ]
            );
        });
    }

    public function rejectedIdentity(?string $reason = 'Documento no coincide'): static
    {
        return $this->afterCreating(function (ProfessionalProfile $profile) use ($reason) {
            ProfessionalIdentityVerification::updateOrCreate(
                ['professional_id' => $profile->id],
                [
                    'verification_provider' => 'didit',
                    'provider_verification_id' => fake()->uuid(),
                    'provider_session_id' => fake()->uuid(),
                    'status' => IdentityVerificationStatus::REJECTED,
                    'provider_status' => 'declined',
                    'started_at' => now(),
                    'rejected_at' => now(),
                    'reason_code' => 'document_mismatch',
                    'review_reason' => $reason,
                ]
            );
        });
    }

    public function expiredIdentity(): static
    {
        return $this->afterCreating(function (ProfessionalProfile $profile) {
            ProfessionalIdentityVerification::updateOrCreate(
                ['professional_id' => $profile->id],
                [
                    'verification_provider' => 'didit',
                    'provider_verification_id' => fake()->uuid(),
                    'provider_session_id' => fake()->uuid(),
                    'status' => IdentityVerificationStatus::VERIFIED,
                    'provider_status' => 'approved',
                    'started_at' => now()->subYear(),
                    'verified_at' => now()->subYear(),
                    'expires_at' => now()->subDay(),
                ]
            );
        });
    }

    public function notStartedIdentity(): static
    {
        return $this->afterCreating(function (ProfessionalProfile $profile) {
            ProfessionalIdentityVerification::updateOrCreate(
                ['professional_id' => $profile->id],
                [
                    'verification_provider' => 'didit',
                    'provider_verification_id' => fake()->uuid(),
                    'provider_session_id' => fake()->uuid(),
                    'status' => IdentityVerificationStatus::NOT_STARTED,
                    'provider_status' => 'not_started',
                    'started_at' => now(),
                ]
            );
        });
    }
}
