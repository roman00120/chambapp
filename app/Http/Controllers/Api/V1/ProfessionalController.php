<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\InvitationStatus;
use App\Enums\JobStatus;
use App\Exceptions\IdentityVerificationRequiredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Professional\StoreServiceRequest;
use App\Http\Requests\Professional\UpdateProfessionalProfileRequest;
use App\Http\Requests\Professional\UpdateServiceRequest;
use App\Http\Requests\StoreJobQuoteRequest;
use App\Http\Requests\UpdateAvailabilityRequest;
use App\Http\Resources\Api\V1\JobInvitationResource;
use App\Http\Resources\Api\V1\JobQuoteResource;
use App\Http\Resources\Api\V1\JobRequestResource;
use App\Http\Resources\Api\V1\ProfessionalResource;
use App\Http\Resources\Api\V1\ServiceResource;
use App\Models\JobInvitation;
use App\Models\JobQuote;
use App\Models\JobRequest;
use App\Models\Service;
use App\Services\JobWorkflowService;
use App\Services\OnDemandMatchingService;
use App\Services\ProfessionalAvailabilityService;
use App\Services\ProfessionalProfileService;
use App\Services\ProfessionalServiceManager;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ProfessionalController extends Controller
{
    public function profile(Request $request, ProfessionalProfileService $profiles): ProfessionalResource
    {
        $profile = $profiles->profileFor($request->user())->load('user');
        $this->authorize('view', $profile);

        return new ProfessionalResource($profile);
    }

    public function updateProfile(
        UpdateProfessionalProfileRequest $request,
        ProfessionalProfileService $profiles,
    ): ProfessionalResource {
        $profile = $profiles->profileFor($request->user());
        $this->authorize('update', $profile);

        return new ProfessionalResource($profiles->update(
            $request->user(),
            $profile,
            $request->validated(),
            $request->file('profile_photo'),
        ));
    }

    public function jobs(Request $request, ProfessionalProfileService $profiles): AnonymousResourceCollection
    {
        $profile = $profiles->profileFor($request->user());
        $this->authorize('view', $profile);
        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(JobStatus::class)],
        ]);

        return JobRequestResource::collection(
            $profile->jobRequests()
                ->with(['category', 'service.category', 'service.coverImage', 'professional.user', 'payments', 'review.client'])
                ->when(
                    $validated['status'] ?? null,
                    fn ($query, string $status) => $query->where('status', $status),
                )
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        );
    }

    public function services(Request $request, ProfessionalProfileService $profiles): AnonymousResourceCollection
    {
        $profile = $profiles->profileFor($request->user());

        return ServiceResource::collection(
            $profile->services()->with(['category', 'professional.user', 'coverImage'])->latest()->paginate(15),
        );
    }

    public function storeService(
        StoreServiceRequest $request,
        ProfessionalProfileService $profiles,
        ProfessionalServiceManager $services,
    ): JsonResponse {
        try {
            $service = $services->create(
                $profiles->profileFor($request->user()),
                $request->validated(),
                $request->file('images', []),
            );
        } catch (DomainException $exception) {
            return $this->domainError($exception, 'SERVICE_UNAVAILABLE');
        }

        return response()->json([
            'data' => new ServiceResource($service),
            'message' => 'Servicio publicado correctamente.',
        ], 201);
    }

    public function showService(Service $service): ServiceResource
    {
        $this->authorize('view', $service);

        return new ServiceResource($service->load(['category', 'professional.user', 'images', 'coverImage']));
    }

    public function updateService(
        UpdateServiceRequest $request,
        Service $service,
        ProfessionalServiceManager $services,
    ): ServiceResource {
        $this->authorize('update', $service);

        return new ServiceResource($services->update($service, $request->validated(), $request->file('images', [])));
    }

    public function destroyService(Service $service): JsonResponse
    {
        $this->authorize('delete', $service);
        $service->delete();

        return response()->json(['data' => null, 'message' => 'Servicio eliminado correctamente.']);
    }

    public function availability(Request $request, ProfessionalProfileService $profiles): JsonResponse
    {
        $profile = $profiles->profileFor($request->user());

        return response()->json(['data' => [
            'is_available' => $profile->is_available,
            'availability_status' => $profile->availability_status?->value,
            'service_radius_km' => $profile->service_radius_km,
            'latitude' => $profile->last_latitude,
            'longitude' => $profile->last_longitude,
            'location_updated_at' => $profile->location_updated_at?->toIso8601String(),
        ]]);
    }

    public function updateAvailability(
        UpdateAvailabilityRequest $request,
        ProfessionalProfileService $profiles,
        ProfessionalAvailabilityService $availability,
    ): JsonResponse {
        try {
            $profile = $availability->update($profiles->profileFor($request->user()), $request->validated());
        } catch (DomainException $exception) {
            return $this->domainError($exception, 'LOCATION_REQUIRED');
        }

        return response()->json(['data' => [
            'is_available' => $profile->is_available,
            'availability_status' => $profile->availability_status?->value,
            'service_radius_km' => $profile->service_radius_km,
            'location_updated_at' => $profile->location_updated_at?->toIso8601String(),
        ], 'message' => 'Disponibilidad actualizada correctamente.']);
    }

    public function invitations(Request $request, ProfessionalProfileService $profiles): AnonymousResourceCollection
    {
        $profile = $profiles->profileFor($request->user());
        $invitations = JobInvitation::query()
            ->with(['jobRequest.category'])
            ->where('professional_id', $profile->getKey())
            ->open()
            ->where('expires_at', '>=', now())
            ->latest('invited_at')
            ->paginate(15);
        JobInvitation::query()->whereIn('id', $invitations->pluck('id'))
            ->where('status', InvitationStatus::PENDING->value)
            ->update(['status' => InvitationStatus::VIEWED, 'viewed_at' => now()]);

        return JobInvitationResource::collection($invitations);
    }

    public function acceptInvitation(
        Request $request,
        JobInvitation $invitation,
        OnDemandMatchingService $matching,
    ): JsonResponse {
        try {
            $job = $matching->acceptInvitation($invitation, $request->user());
        } catch (DomainException $exception) {
            $message = mb_strtolower($exception->getMessage());
            $code = str_contains($message, 'tomada') ? 'JOB_ALREADY_TAKEN'
                : (str_contains($message, 'ubicación') || str_contains($message, 'radio') ? 'LOCATION_STALE' : 'PROFESSIONAL_BUSY');

            return $this->domainError($exception, $code);
        }

        return response()->json([
            'data' => new JobRequestResource($job),
            'message' => 'Invitación aceptada correctamente.',
        ]);
    }

    public function declineInvitation(
        Request $request,
        JobInvitation $invitation,
        OnDemandMatchingService $matching,
    ): JsonResponse {
        try {
            $matching->declineInvitation($invitation, $request->user());
        } catch (DomainException $exception) {
            return $this->domainError($exception, 'INVITATION_UNAVAILABLE');
        }

        return response()->json(['data' => null, 'message' => 'Invitación rechazada correctamente.']);
    }

    public function createQuote(
        StoreJobQuoteRequest $request,
        JobRequest $job,
        JobWorkflowService $workflow,
    ): JsonResponse {
        $this->authorize('create', [JobQuote::class, $job]);
        try {
            $quote = $workflow->createQuote($job, $request->user(), $request->validated('amount'), $request->validated('description'));
        } catch (DomainException $exception) {
            return $this->domainError($exception, 'QUOTE_UNAVAILABLE');
        }

        return response()->json([
            'data' => new JobQuoteResource($quote),
            'message' => 'Cotización enviada correctamente.',
        ], 201);
    }

    private function domainError(DomainException $exception, string $code): JsonResponse
    {
        if ($exception instanceof IdentityVerificationRequiredException) {
            $code = 'IDENTITY_VERIFICATION_REQUIRED';
        }

        return response()->json([
            'message' => $exception->getMessage(),
            'errors' => (object) [],
            'code' => $code,
        ], $exception instanceof IdentityVerificationRequiredException ? 403 : 409);
    }
}
