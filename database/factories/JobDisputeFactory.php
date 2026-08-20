<?php

namespace Database\Factories;

use App\Enums\JobDisputeStatus;
use App\Models\JobDispute;
use App\Models\JobRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobDispute> */
class JobDisputeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'job_request_id' => JobRequest::factory(),
            'opened_by' => User::factory()->client(),
            'reason' => 'incomplete_work',
            'description' => 'El trabajo requiere una revisión.',
            'status' => JobDisputeStatus::OPEN,
            'resolved_by' => null,
            'resolved_at' => null,
        ];
    }
}
