<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobQuoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $money = app(\App\Services\PaymentCalculationService::class)->calculateJob((string) $this->amount);
        $user = $request->user();
        $isClient = $this->jobRequest?->client_id === $user?->getKey();
        $isProfessional = $this->professional?->user_id === $user?->getKey();
        $isAdmin = $user?->isAdmin() === true;
        $breakdown = [
            'base_amount' => $money->baseAmount,
            'currency' => $money->currency,
        ];
        if ($isClient || $isAdmin) {
            $breakdown += [
                'client_service_fee_percent' => $money->clientServiceFeePercent,
                'client_service_fee' => $money->clientServiceFee,
                'customer_total' => $money->customerTotal,
            ];
        }
        if ($isProfessional || $isAdmin) {
            $breakdown += [
                'professional_commission_percent' => $money->professionalCommissionPercent,
                'professional_commission' => $money->professionalCommission,
                'professional_amount_before_external_costs' => $money->professionalAmountBeforeExternalCosts,
            ];
        }
        if ($isAdmin) {
            $breakdown['platform_gross_fee'] = $money->platformGrossFee;
        }

        return [
            'id' => $this->id,
            'job_id' => $this->job_request_id,
            'professional_id' => $this->professional_id,
            'amount' => (string) $this->amount,
            'currency' => config('chambapp.payments.currency'),
            'economic_breakdown' => $breakdown,
            'description' => $this->description,
            'status' => $this->status?->value,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'rejected_at' => $this->rejected_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
