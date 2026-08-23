<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $images = $this->relationLoaded('images')
            ? $this->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => url(Storage::disk('public')->url($image->path)),
                'alt' => $image->alt_text,
                'is_cover' => $image->is_cover,
            ])->values()
            : null;

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'price_type' => $this->price_type?->value,
            'price' => $this->price !== null ? (string) $this->price : null,
            'currency' => config('chambapp.payments.currency'),
            'is_active' => $this->when($request->user()?->id === $this->professional?->user_id, $this->is_active),
            'is_featured' => $this->is_featured,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'professional' => new ProfessionalResource($this->whenLoaded('professional')),
            'cover_image_url' => $this->coverImage?->path ? url(Storage::disk('public')->url($this->coverImage->path)) : null,
            'images' => $this->when($images !== null, $images),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
