<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_id' => $this->job_request_id,
            'provider' => $this->provider,
            'kind' => $this->kind?->value,
            'currency' => $this->currency,
            'gross_amount' => (string) $this->gross_amount,
            'platform_fee_percent' => (string) $this->platform_fee_percent,
            'platform_fee' => (string) $this->platform_fee,
            'provider_fee' => $this->provider_fee !== null ? (string) $this->provider_fee : null,
            'professional_amount' => (string) $this->professional_amount,
            'status' => $this->status?->value,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'refunded_at' => $this->refunded_at?->toIso8601String(),
            'refunded_amount' => (string) $this->refunded_amount,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
