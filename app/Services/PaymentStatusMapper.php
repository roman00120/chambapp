<?php

namespace App\Services;

use App\Enums\PaymentStatus;

class PaymentStatusMapper
{
    public function map(?string $providerStatus): PaymentStatus
    {
        return match (strtolower((string) $providerStatus)) {
            'approved' => PaymentStatus::APPROVED,
            'rejected' => PaymentStatus::REJECTED,
            'cancelled', 'cancelled_by_user' => PaymentStatus::CANCELLED,
            'refunded' => PaymentStatus::REFUNDED,
            'partially_refunded' => PaymentStatus::PARTIALLY_REFUNDED,
            'charged_back' => PaymentStatus::CHARGED_BACK,
            'in_mediation' => PaymentStatus::IN_MEDIATION,
            'in_process', 'pending', 'authorized' => PaymentStatus::PROCESSING,
            default => PaymentStatus::PROCESSING,
        };
    }
}
