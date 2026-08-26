<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->resource->relationLoaded('professionalProfile') ? $this->professionalProfile : $this->resource->professionalProfile;
        $avatar = $profile?->profilePhotoUrl() ?? $this->avatar_url;

        $requestedMode = $request->header('X-Chambapp-Mode') ?: $request->input('active_mode');
        $activeMode = $this->resource->resolveActiveMode($requestedMode);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role?->value,
            'capabilities' => $this->resource->capabilities(),
            'active_mode' => $activeMode,
            'avatar' => $avatar,
            'profile_photo_url' => $avatar,
            'phone' => $this->when($request->user()?->is($this->resource), $this->phone),
            'status' => $this->status?->value,
            'email_verified' => $this->when($request->user()?->is($this->resource), $this->hasVerifiedEmail()),
            'professional_profile' => $this->when(
                $profile !== null,
                fn () => new ProfessionalResource($profile),
            ),
            'achievements' => app(\App\Services\AchievementService::class)->getPublicAchievementsForUser($this->resource),
        ];
    }
}
