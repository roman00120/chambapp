<?php

namespace Database\Factories;

use App\Enums\VerificationStatus;
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
            'user_id' => User::factory()->professional(),
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
}
