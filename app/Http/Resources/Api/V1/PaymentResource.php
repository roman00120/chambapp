<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isAdmin = $user?->isAdmin() === true;
        $isClient = $this->client_id === $user?->getKey();
        $isProfessional = $this->professional?->user_id === $user?->getKey();
        $isV2 = $this->economic_model_version === 'client_15_professional_15';
        $baseAmount = (string) ($this->base_amount ?? $this->gross_amount);
        $customerTotal = (string) ($this->customer_total ?? $this->gross_amount);
        $professionalBeforeCosts = (string) ($this->professional_amount_before_external_costs ?? $this->professional_amount);

        $data = [
            'id' => $this->id,
            'job_id' => $this->job_request_id,
            'provider' => $this->provider,
            'kind' => $this->kind?->value,
            'currency' => $this->currency,
            'economic_model_version' => $this->economic_model_version ?? 'single_platform_fee_15',
            'base_amount' => $baseAmount,
            'status' => $this->status?->value,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'refunded_at' => $this->refunded_at?->toIso8601String(),
            'refunded_amount' => (string) $this->refunded_amount,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];

        if ($isClient || $isAdmin) {
            $data += [
                'client_service_fee_percent' => $isV2 ? (string) $this->client_service_fee_percent : '0.00',
                'client_service_fee' => $isV2 ? (string) $this->client_service_fee : '0.00',
                'customer_total' => $customerTotal,
            ];
        }

        if ($isProfessional || $isAdmin) {
            $data += [
                'professional_commission_percent' => $isV2 ? (string) $this->professional_commission_percent : (string) $this->platform_fee_percent,
                'professional_commission' => $isV2 ? (string) $this->professional_commission : (string) $this->platform_fee,
                'professional_amount_before_external_costs' => $professionalBeforeCosts,
                'provider_fee' => $this->provider_fee !== null ? (string) $this->provider_fee : null,
                'professional_settlement_amount' => (string) $this->professional_amount,
            ];
        }

        if ($isAdmin) {
            $data += [
                'platform_gross_fee' => (string) ($this->platform_gross_fee ?? $this->platform_fee),
                'gross_amount' => (string) $this->gross_amount,
                'platform_fee_percent' => (string) $this->platform_fee_percent,
                'platform_fee' => (string) $this->platform_fee,
                'professional_amount' => (string) $this->professional_amount,
            ];
        }

        if (! $isV2) {
            $data += [
                'gross_amount' => (string) $this->gross_amount,
                'platform_fee_percent' => (string) $this->platform_fee_percent,
                'platform_fee' => (string) $this->platform_fee,
                'professional_amount' => (string) $this->professional_amount,
            ];
        }

        return $data;
    }
}
