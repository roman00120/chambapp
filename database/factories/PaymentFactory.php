<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'job_request_id' => JobRequest::factory(),
            'client_id' => User::factory()->client(),
            'professional_id' => ProfessionalProfile::factory(),
            'provider' => null,
            'external_payment_id' => null,
            'external_reference' => 'CHAMB-'.fake()->unique()->numerify('######'),
            'currency' => 'MXN',
            'gross_amount' => '650.00',
            'platform_fee_percent' => '15.00',
            'platform_fee' => '65.00',
            'provider_fee' => null,
            'professional_amount' => '565.00',
            'status' => PaymentStatus::PENDING,
            'paid_at' => null,
            'refunded_at' => null,
        ];
    }
}
