<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\JobRequest;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Conversation> */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'job_request_id' => JobRequest::factory(),
            'client_id' => User::factory()->client(),
            'professional_id' => ProfessionalProfile::factory(),
        ];
    }
}
