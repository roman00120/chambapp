<?php

namespace Database\Factories;

use App\Enums\JobStatus;
use App\Models\JobRequest;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobRequest> */
class JobRequestFactory extends Factory
{
    protected $model = JobRequest::class;

    public function definition(): array
    {
        return [
            'client_id' => User::factory()->client(),
            'professional_id' => ProfessionalProfile::factory(),
            'service_id' => null,
            'title' => fake()->randomElement(['Reparación en casa', 'Mantenimiento urgente', 'Instalación para negocio']),
            'description' => fake()->paragraph(),
            'address' => fake()->streetAddress(),
            'city' => 'Ciudad de México',
            'state' => 'Ciudad de México',
            'postal_code' => fake()->numerify('#####'),
            'latitude' => '19.4326080',
            'longitude' => '-99.1332090',
            'requested_date' => now()->addDays(fake()->numberBetween(1, 15)),
            'agreed_price' => '650.00',
            'status' => JobStatus::PENDING,
            'accepted_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => JobStatus::COMPLETED,
            'accepted_at' => now()->subDays(5),
            'started_at' => now()->subDays(3),
            'completed_at' => now()->subDay(),
        ]);
    }
}
