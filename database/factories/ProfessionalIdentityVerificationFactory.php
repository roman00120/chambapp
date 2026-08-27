<?php

namespace Database\Factories;

use App\Enums\IdentityVerificationStatus;
use App\Models\ProfessionalIdentityVerification;
use App\Models\ProfessionalProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProfessionalIdentityVerification> */
class ProfessionalIdentityVerificationFactory extends Factory
{
    protected $model = ProfessionalIdentityVerification::class;

    public function definition(): array
    {
        return [
            'professional_id' => fn () => ProfessionalProfile::factory(),
            'verification_provider' => 'didit',
            'provider_verification_id' => fake()->uuid(),
            'provider_session_id' => fake()->uuid(),
            'status' => IdentityVerificationStatus::NOT_STARTED,
            'provider_status' => 'not_started',
            'started_at' => now(),
            'verification_level' => '1',
            'document_type' => 'national_id',
        ];
    }

    public function verified(): static
    {
        return $this->state([
            'status' => IdentityVerificationStatus::VERIFIED,
            'provider_status' => 'approved',
            'verified_at' => now(),
            'expires_at' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state([
            'status' => IdentityVerificationStatus::PENDING,
            'provider_status' => 'pending',
            'submitted_at' => now(),
        ]);
    }

    public function needsReview(): static
    {
        return $this->state([
            'status' => IdentityVerificationStatus::NEEDS_REVIEW,
            'provider_status' => 'needs_review',
            'submitted_at' => now(),
        ]);
    }

    public function rejected(?string $reason = 'Documento ilegible'): static
    {
        return $this->state([
            'status' => IdentityVerificationStatus::REJECTED,
            'provider_status' => 'declined',
            'rejected_at' => now(),
            'reason_code' => 'document_unreadable',
            'review_reason' => $reason,
        ]);
    }

    public function expired(): static
    {
        return $this->state([
            'status' => IdentityVerificationStatus::VERIFIED,
            'provider_status' => 'approved',
            'verified_at' => now()->subYear(),
            'expires_at' => now()->subDay(),
        ]);
    }

    public function notStarted(): static
    {
        return $this->state([
            'status' => IdentityVerificationStatus::NOT_STARTED,
            'provider_status' => 'not_started',
            'verified_at' => null,
            'rejected_at' => null,
            'expires_at' => null,
        ]);
    }
}
