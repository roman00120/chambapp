<?php

namespace App\Services;

use App\Enums\JobStatus;
use App\Enums\ServiceMode;
use App\Models\JobRequest;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class JobRequestService
{
    public function __construct(private readonly OnDemandMatchingService $matching) {}

    public function createImmediate(User $client, array $data, array $photos = []): JobRequest
    {
        $service = $this->safeService($data['service_id'] ?? null, (int) $data['category_id']);
        if ($service && $service->professional && $service->professional->user_id === $client->getKey()) {
            throw new \DomainException('No puedes solicitar un servicio a tu propio perfil profesional.');
        }
        $paths = collect($photos)
            ->filter(fn ($photo) => $photo instanceof UploadedFile)
            ->map(fn (UploadedFile $photo) => Storage::disk('local')->putFile('on-demand/'.$client->getKey(), $photo))
            ->values()
            ->all();
        $job = JobRequest::query()->create([
            'client_id' => $client->getKey(),
            'professional_id' => null,
            'service_id' => $service?->getKey(),
            'category_id' => $data['category_id'],
            'service_mode' => ServiceMode::IMMEDIATE,
            'title' => $data['title'],
            'description' => $data['description'],
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'requested_date' => now(),
            'status' => JobStatus::SEARCHING,
            'photo_paths' => $paths ?: null,
        ]);

        return $this->matching->startSearch($job);
    }

    public function createScheduled(User $client, array $data): JobRequest
    {
        $service = $this->safeService($data['service_id'] ?? null, isset($data['category_id']) ? (int) $data['category_id'] : null);
        if ($service && $service->professional && $service->professional->user_id === $client->getKey()) {
            throw new \DomainException('No puedes solicitar un servicio a tu propio perfil profesional.');
        }

        $job = JobRequest::query()->create([
            'client_id' => $client->getKey(),
            'professional_id' => $service?->professional_id,
            'service_id' => $service?->getKey(),
            'category_id' => $service?->category_id ?? $data['category_id'],
            'service_mode' => ServiceMode::from($data['service_mode'] ?? 'scheduled'),
            'title' => $data['title'],
            'description' => $data['description'],
            'address' => $data['address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'postal_code' => $data['postal_code'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'requested_date' => $data['scheduled_for'],
            'scheduled_for' => $data['scheduled_for'],
            'scheduled_slot' => $data['scheduled_slot'],
            'status' => JobStatus::PENDING,
        ]);

        if ($service?->professional?->user) {
            $service->professional->user->notify(new \App\Notifications\DirectServiceRequestedNotification($job->loadMissing(['client', 'service'])));
        }

        return $job;
    }

    private function safeService(?int $serviceId, ?int $categoryId = null): ?Service
    {
        if (! $serviceId) {
            return null;
        }

        $query = Service::query()->with(['professional.user']);
        if ($categoryId !== null && $categoryId > 0) {
            // If categoryId is given, prefer matching service directly or within category
            $service = (clone $query)->where('category_id', $categoryId)->find($serviceId);
            if ($service) {
                return $service;
            }
        }

        return $query->find($serviceId);
    }
}
