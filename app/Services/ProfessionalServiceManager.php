<?php

namespace App\Services;

use App\Enums\PriceType;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfessionalServiceManager
{
    public function __construct(
        private readonly ServiceImageManager $images,
        private readonly ProfessionalIdentityVerificationService $identityVerification,
    ) {}

    public function create(ProfessionalProfile $profile, array $data, array $files = []): Service
    {
        $this->identityVerification->ensureProfessionalCanAcceptJobs($profile);

        return DB::transaction(function () use ($profile, $data, $files): Service {
            $service = $profile->services()->create([
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($profile, $data['title']),
                'description' => $data['description'],
                'price_type' => $data['price_type'],
                'price' => $data['price_type'] === PriceType::QUOTE->value ? null : ($data['price'] ?? null),
                'is_active' => true,
                'is_featured' => false,
            ]);
            $this->images->store($service, $files, isset($data['cover_index']) ? (int) $data['cover_index'] : null);

            return $service->fresh(['category', 'professional.user', 'images', 'coverImage']);
        });
    }

    public function update(Service $service, array $data, array $files = []): Service
    {
        $cover = ! empty($data['cover_image_id'])
            ? $service->images()->whereKey($data['cover_image_id'])->firstOrFail()
            : null;

        DB::transaction(function () use ($service, $data, $files, $cover): void {
            $service->update([
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'price_type' => $data['price_type'],
                'price' => $data['price_type'] === PriceType::QUOTE->value ? null : ($data['price'] ?? null),
            ]);
            $this->images->store($service, $files, isset($data['cover_index']) ? (int) $data['cover_index'] : null);
            if ($cover) {
                $this->images->setCover($service, $cover);
            }
        });

        return $service->fresh(['category', 'professional.user', 'images', 'coverImage']);
    }

    private function uniqueSlug(ProfessionalProfile $profile, string $title): string
    {
        $base = Str::limit(Str::slug($title), 70, '');
        do {
            $slug = trim($base, '-').'-'.Str::lower(Str::random(8));
        } while ($profile->services()->where('slug', $slug)->exists());

        return $slug;
    }
}
