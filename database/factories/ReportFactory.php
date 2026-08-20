<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Models\ProfessionalProfile;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Report> */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'reporter_id' => User::factory()->client(),
            'reportable_type' => ProfessionalProfile::class,
            'reportable_id' => ProfessionalProfile::factory(),
            'reason' => 'Información desactualizada',
            'description' => fake()->sentence(12),
            'status' => ReportStatus::PENDING,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}
