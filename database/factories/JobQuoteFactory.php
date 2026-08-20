<?php

namespace Database\Factories;

use App\Enums\QuoteStatus;
use App\Models\JobQuote;
use App\Models\JobRequest;
use App\Models\ProfessionalProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobQuote> */
class JobQuoteFactory extends Factory
{
    protected $model = JobQuote::class;

    public function definition(): array
    {
        return [
            'job_request_id' => JobRequest::factory(),
            'professional_id' => ProfessionalProfile::factory(),
            'amount' => '850.00',
            'description' => 'Incluye instalación y materiales básicos.',
            'status' => QuoteStatus::PENDING,
            'expires_at' => now()->addHours(48),
            'accepted_at' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ];
    }
}
