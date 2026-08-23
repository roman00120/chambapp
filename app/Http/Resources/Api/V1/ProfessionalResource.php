<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProfessionalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user?->name,
            'avatar' => $this->profile_photo ? url(Storage::disk('public')->url($this->profile_photo)) : $this->user?->avatar_url,
            'bio' => $this->bio,
            'experience_years' => $this->experience_years,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'rating' => (string) $this->average_rating,
            'total_reviews' => $this->total_reviews,
            'completed_jobs' => $this->total_completed_jobs,
            'verification_status' => $this->verification_status?->value,
            'verified' => $this->isPubliclyVisible(),
            'is_available' => $this->when($request->user()?->id === $this->user_id, $this->is_available),
            'availability_status' => $this->when($request->user()?->id === $this->user_id, $this->availability_status?->value),
            'service_radius_km' => $this->when($request->user()?->id === $this->user_id, $this->service_radius_km),
            'location_updated_at' => $this->when($request->user()?->id === $this->user_id, $this->location_updated_at?->toIso8601String()),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'recent_reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
