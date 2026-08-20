<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PaymentTransaction> */
class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'event_type' => 'payment.created',
            'provider_event_id' => 'demo_'.fake()->unique()->numerify('######'),
            'payload' => ['source' => 'factory', 'version' => 1],
        ];
    }
}
