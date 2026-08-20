<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ServiceImageManager
{
    public const MAX_IMAGES = 5;

    public function store(Service $service, array $files, ?int $coverIndex = null): void
    {
        $existingCount = $service->images()->count();

        if ($existingCount + count($files) > self::MAX_IMAGES) {
            throw ValidationException::withMessages([
                'images' => 'Solo puedes subir hasta 5 imágenes.',
            ]);
        }

        $disk = Storage::disk('public');
        $storedPaths = [];
        $hasCover = $service->images()->where('is_cover', true)->exists();
        $selectedCover = null;

        try {
            foreach ($files as $index => $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $path = $file->store('services/'.$service->getKey(), 'public');
                $storedPaths[] = $path;
                $shouldBeCover = ! $hasCover
                    && (($coverIndex !== null && $coverIndex === $index) || ($coverIndex === null && $index === 0));

                $createdImage = ServiceImage::create([
                    'service_id' => $service->getKey(),
                    'path' => $path,
                    'alt_text' => $service->title,
                    'sort_order' => $existingCount + $index,
                    'is_cover' => $shouldBeCover,
                ]);

                if ($coverIndex !== null && $coverIndex === $index) {
                    $selectedCover = $createdImage;
                }

                $hasCover = $hasCover || $shouldBeCover;
            }
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                $disk->delete($path);
            }

            throw $exception;
        }

        if ($selectedCover) {
            $service->images()->update(['is_cover' => false]);
            $selectedCover->update(['is_cover' => true]);
        }

        $this->ensureCover($service);
    }

    public function setCover(Service $service, ServiceImage $image): void
    {
        abort_unless($image->service_id === $service->getKey(), 404);

        $service->images()->update(['is_cover' => false]);
        $image->update(['is_cover' => true]);
    }

    public function remove(ServiceImage $image): void
    {
        $service = $image->service;
        $wasCover = $image->is_cover;

        Storage::disk('public')->delete($image->path);
        $image->delete();

        if ($wasCover) {
            $this->ensureCover($service);
        }
    }

    public function ensureCover(Service $service): void
    {
        $cover = $service->images()->where('is_cover', true)->first();

        if ($cover) {
            return;
        }

        $firstImage = $service->images()->orderBy('sort_order')->orderBy('id')->first();
        $firstImage?->update(['is_cover' => true]);
    }
}
