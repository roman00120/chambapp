<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'distance_km' => (string) $this->distance_km,
            'invited_at' => $this->invited_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'job' => $this->whenLoaded('jobRequest', fn () => [
                'id' => $this->jobRequest->id,
                'title' => $this->jobRequest->title,
                'description' => $this->jobRequest->description,
                'city' => $this->jobRequest->city,
                'state' => $this->jobRequest->state,
                'category' => new CategoryResource($this->jobRequest->category),
            ]),
        ];
    }
}
