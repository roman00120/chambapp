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
    public function __construct(
        private readonly OnDemandMatchingService $matching,
        private readonly PaymentCalculationService $paymentCalculation,
    ) {}

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
        $service = null;
        $professionalId = null;
        $categoryId = isset($data['category_id']) ? (int) $data['category_id'] : null;

        if (! empty($data['service_id'])) {
            $service = Service::query()
                ->with(['professional.user', 'category'])
                ->find((int) $data['service_id']);

            if ($service) {
                $professionalId = $service->professional_id;
                $categoryId = $service->category_id;

                if ($service->professional && $service->professional->user_id === $client->getKey()) {
                    throw new \DomainException('No puedes solicitar un servicio a tu propio perfil profesional.');
                }
            }
        }

        $status = JobStatus::PENDING;
        $agreedPrice = null;
        $economicModelVersion = null;
        $baseAmount = null;
        $clientFeePercent = null;
        $clientFee = null;
        $proCommissionPercent = null;
        $proCommission = null;
        $customerTotal = null;
        $platformGrossFee = null;
        $proAmountBeforeCosts = null;

        if ($service && $service->price !== null && (float) $service->price > 0) {
            $status = JobStatus::AWAITING_PAYMENT;
            $money = app(\App\Services\PaymentCalculationService::class)->calculateJob((string) $service->price);
            $agreedPrice = $money->baseAmount;
            $economicModelVersion = $money->economicModelVersion;
            $baseAmount = $money->baseAmount;
            $clientFeePercent = $money->clientServiceFeePercent;
            $clientFee = $money->clientServiceFee;
            $proCommissionPercent = $money->professionalCommissionPercent;
            $proCommission = $money->professionalCommission;
            $customerTotal = $money->customerTotal;
            $platformGrossFee = $money->platformGrossFee;
            $proAmountBeforeCosts = $money->professionalAmountBeforeExternalCosts;
        }

        $money = null;
        if ($service && $service->price !== null) {
            $money = $this->paymentCalculation->calculateJob((string) $service->price);
        }

        $job = JobRequest::query()->create([
            'client_id' => $client->getKey(),
            'professional_id' => $professionalId,
            'service_id' => $service?->getKey(),
            'category_id' => $categoryId,
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
            'status' => $service ? JobStatus::AWAITING_PAYMENT : JobStatus::PENDING,
            'agreed_price' => $money?->baseAmount,
            'economic_model_version' => $money?->economicModelVersion,
            'base_amount' => $money?->baseAmount,
            'client_service_fee_percent' => $money?->clientServiceFeePercent,
            'client_service_fee' => $money?->clientServiceFee,
            'professional_commission_percent' => $money?->professionalCommissionPercent,
            'professional_commission' => $money?->professionalCommission,
            'customer_total' => $money?->customerTotal,
            'platform_gross_fee' => $money?->platformGrossFee,
            'professional_amount_before_external_costs' => $money?->professionalAmountBeforeExternalCosts,
        ]);

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
