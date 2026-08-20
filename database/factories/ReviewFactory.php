<?php

namespace Database\Factories;

use App\Models\JobRequest;
use App\Models\ProfessionalProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Review> */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'job_request_id' => JobRequest::factory()->completed(),
            'client_id' => User::factory()->client(),
            'professional_id' => ProfessionalProfile::factory(),
            'rating' => fake()->numberBetween(4, 5),
            'comment' => fake()->sentence(12),
        ];
    }
}
