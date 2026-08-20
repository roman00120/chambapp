<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->resource->relationLoaded('professionalProfile') ? $this->professionalProfile : null;
        $avatar = $this->avatar_url;
        if (! $avatar && $profile && $profile->profile_photo) {
            $avatar = url(Storage::disk('public')->url($profile->profile_photo));
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role?->value,
            'avatar' => $avatar,
            'phone' => $this->when($request->user()?->is($this->resource), $this->phone),
            'status' => $this->status?->value,
            'email_verified' => $this->when($request->user()?->is($this->resource), $this->hasVerifiedEmail()),
            'professional_profile' => $this->when(
                $profile !== null,
                fn () => new ProfessionalResource($profile),
            ),
        ];
    }
}
