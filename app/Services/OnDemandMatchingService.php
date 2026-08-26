<?php

namespace App\Services;

use App\Enums\AvailabilityStatus;
use App\Enums\InvitationStatus;
use App\Enums\JobStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\JobInvitation;
use App\Models\JobRequest;
use App\Models\ProfessionalProfile;
use App\Models\User;
use App\Notifications\ChambappNotification;
use DomainException;
use Illuminate\Support\Facades\DB;

class OnDemandMatchingService
{
    public function __construct(
        private readonly GeoDistanceService $distance,
        private readonly ProfessionalIdentityVerificationService $identityVerification,
    ) {}

    public function startSearch(JobRequest $job): JobRequest
    {
        DB::transaction(function () use ($job): void {
            $locked = JobRequest::query()->lockForUpdate()->findOrFail($job->getKey());
            if (! $locked->isImmediate()) {
                throw new DomainException('Este trabajo no usa el modo inmediato.');
            }

            $now = now();
            $locked->forceFill([
                'status' => JobStatus::SEARCHING,
                'search_started_at' => $now,
                'search_expires_at' => $now->copy()->addMinutes((int) config('chambapp.on_demand.immediate_request_timeout_minutes', 5)),
                'search_round' => 1,
                'search_radius_km' => config('chambapp.on_demand.search_radii_km.0', 5),
            ])->save();
        });

        $fresh = $job->fresh(['category', 'service']);
        $this->inviteCandidates($fresh);

        return $fresh->fresh();
    }

    public function refresh(JobRequest $job): JobRequest
    {
        $shouldInvite = false;
        $fresh = DB::transaction(function () use ($job, &$shouldInvite): JobRequest {
            $locked = JobRequest::query()->lockForUpdate()->findOrFail($job->getKey());
            $this->expireInvitations($locked);

            if ($locked->status !== JobStatus::SEARCHING) {
                return $locked->fresh(['category', 'service', 'professional.user', 'quotes']);
            }
            if ($locked->search_expires_at?->isPast()) {
                $locked->forceFill(['status' => JobStatus::EXPIRED])->save();
                $locked->invitations()->open()->update([
                    'status' => InvitationStatus::LOST,
                    'responded_at' => now(),
                ]);

                return $locked->fresh(['category', 'service', 'professional.user', 'quotes']);
            }

            if (! $locked->invitations()->open()->exists()) {
                $radii = config('chambapp.on_demand.search_radii_km', [5, 10, 15, 25]);
                $nextIndex = (int) $locked->search_round;
                $pollSeconds = (int) config('chambapp.on_demand.polling_interval_seconds', 4);
                $nextAt = $locked->search_started_at?->copy()->addSeconds($pollSeconds * $nextIndex);
                if ($nextIndex < count($radii) && (! $nextAt || $nextAt->isPast())) {
                    $locked->forceFill([
                        'search_round' => $nextIndex + 1,
                        'search_radius_km' => $radii[$nextIndex],
                    ])->save();
                    $shouldInvite = true;
                }
            }

            return $locked->fresh(['category', 'service', 'professional.user', 'quotes']);
        });

        if ($shouldInvite) {
            $this->inviteCandidates($fresh);
            $fresh = $fresh->fresh(['category', 'service', 'professional.user', 'quotes']);
        }

        return $fresh;
    }

    public function acceptInvitation(JobInvitation $invitation, User $professional): JobRequest
    {
        return DB::transaction(function () use ($invitation, $professional): JobRequest {
            $lockedInvitation = JobInvitation::query()->lockForUpdate()->findOrFail($invitation->getKey());
            $job = JobRequest::query()->lockForUpdate()->findOrFail($lockedInvitation->job_request_id);
            $profile = ProfessionalProfile::query()->with('user')->lockForUpdate()->findOrFail($lockedInvitation->professional_id);

            $this->identityVerification->ensureProfessionalCanAcceptJobs($profile);

            if ($profile->user_id !== $professional->getKey() || ! $profile->canReceiveImmediateJobs()) {
                throw new DomainException('Ya no puedes aceptar esta chamba.');
            }
            if ($job->client_id === $professional->getKey() || $profile->user_id === $job->client_id) {
                throw new DomainException('No puedes aceptar tu propia solicitud de trabajo.');
            }
            if ($job->status !== JobStatus::SEARCHING || $job->professional_id !== null) {
                throw new DomainException('La chamba ya fue tomada por otro profesional.');
            }
            if (! in_array($lockedInvitation->status, [InvitationStatus::PENDING, InvitationStatus::VIEWED], true) || $lockedInvitation->isExpired()) {
                throw new DomainException('Esta invitación ya no está disponible.');
            }
            if ($job->latitude === null || $job->longitude === null) {
                throw new DomainException('La solicitud no tiene una ubicación válida.');
            }

            $currentDistance = $this->distance->distanceKm(
                (float) $job->latitude,
                (float) $job->longitude,
                (float) $profile->last_latitude,
                (float) $profile->last_longitude,
            );
            if ($currentDistance > min((float) $job->search_radius_km, (float) $profile->service_radius_km)) {
                throw new DomainException('La ubicación ya no está dentro del radio permitido.');
            }

            $job->forceFill([
                'professional_id' => $profile->getKey(),
                'status' => JobStatus::MATCHED,
                'matched_at' => now(),
            ])->save();
            $lockedInvitation->forceFill([
                'status' => InvitationStatus::ACCEPTED,
                'responded_at' => now(),
            ])->save();
            $job->invitations()->open()->update([
                'status' => InvitationStatus::LOST,
                'responded_at' => now(),
            ]);
            $profile->forceFill(['availability_status' => AvailabilityStatus::BUSY])->save();

            $job->client?->notify(new ChambappNotification(
                'on_demand_matched',
                '¡Encontramos un profesional!',
                'Ya puedes recibir su cotización dentro de Chambapp.',
                route('client.ondemand.show', $job),
            ));
            $professional->notify(new ChambappNotification(
                'on_demand_invitation_accepted',
                'Tomaste una chamba',
                'Revisa la solicitud y envía tu cotización.',
                route('job-requests.show', $job),
            ));

            return $job->fresh(['client', 'professional.user', 'category', 'service']);
        });
    }

    public function declineInvitation(JobInvitation $invitation, User $professional): void
    {
        DB::transaction(function () use ($invitation, $professional): void {
            $locked = JobInvitation::query()->with('professional')->lockForUpdate()->findOrFail($invitation->getKey());
            if ($locked->professional?->user_id !== $professional->getKey()) {
                throw new DomainException('Esta invitación no te pertenece.');
            }
            if (! in_array($locked->status, [InvitationStatus::PENDING, InvitationStatus::VIEWED], true)) {
                return;
            }
            $locked->forceFill(['status' => InvitationStatus::DECLINED, 'responded_at' => now()])->save();
        });
    }

    public function cancelSearch(JobRequest $job, User $client): JobRequest
    {
        return DB::transaction(function () use ($job, $client): JobRequest {
            $locked = JobRequest::query()->lockForUpdate()->findOrFail($job->getKey());
            if ($locked->client_id !== $client->getKey() || $locked->status !== JobStatus::SEARCHING) {
                throw new DomainException('Esta búsqueda ya no puede cancelarse.');
            }
            $locked->forceFill(['status' => JobStatus::CANCELLED, 'cancelled_at' => now()])->save();
            $locked->invitations()->open()->update(['status' => InvitationStatus::LOST, 'responded_at' => now()]);

            return $locked->fresh();
        });
    }

    public function searchAgain(JobRequest $job, User $client): JobRequest
    {
        DB::transaction(function () use ($job, $client): void {
            $locked = JobRequest::query()->lockForUpdate()->findOrFail($job->getKey());
            if ($locked->client_id !== $client->getKey() || ! in_array($locked->status, [JobStatus::EXPIRED, JobStatus::CANCELLED, JobStatus::REJECTED], true)) {
                throw new DomainException('Esta solicitud no puede buscarse nuevamente.');
            }
            $locked->invitations()->open()->update(['status' => InvitationStatus::LOST, 'responded_at' => now()]);
            $now = now();
            $locked->forceFill([
                'professional_id' => null,
                'status' => JobStatus::SEARCHING,
                'search_started_at' => $now,
                'search_expires_at' => $now->copy()->addMinutes((int) config('chambapp.on_demand.immediate_request_timeout_minutes', 5)),
                'search_round' => 1,
                'search_radius_km' => config('chambapp.on_demand.search_radii_km.0', 5),
                'matched_at' => null,
                'cancelled_at' => null,
            ])->save();
        });

        $fresh = $job->fresh(['category', 'service']);
        $this->inviteCandidates($fresh);

        return $fresh;
    }

    private function inviteCandidates(JobRequest $job): void
    {
        if ($job->latitude === null || $job->longitude === null || $job->status !== JobStatus::SEARCHING) {
            return;
        }

        $categoryId = $job->category_id ?? $job->service?->category_id;
        if (! $categoryId) {
            return;
        }
        $radius = (float) ($job->search_radius_km ?: config('chambapp.on_demand.search_radii_km.0', 5));
        $latDelta = $radius / 111;
        $lonDelta = $radius / max(1, 111 * cos(deg2rad((float) $job->latitude)));
        $activeJobStatuses = [JobStatus::MATCHED, JobStatus::AWAITING_QUOTE, JobStatus::AWAITING_PAYMENT, JobStatus::PAID, JobStatus::ON_THE_WAY, JobStatus::ARRIVED, JobStatus::IN_PROGRESS, JobStatus::AWAITING_CONFIRMATION];

        $candidates = ProfessionalProfile::query()
            ->with('user')
            ->where('is_available', true)
            ->where('availability_status', AvailabilityStatus::AVAILABLE->value)
            ->whereNotNull('last_latitude')
            ->whereNotNull('last_longitude')
            ->where('location_updated_at', '>=', now()->subMinutes((int) config('chambapp.on_demand.location_freshness_minutes', 30)))
            ->whereBetween('last_latitude', [(float) $job->latitude - $latDelta, (float) $job->latitude + $latDelta])
            ->whereBetween('last_longitude', [(float) $job->longitude - $lonDelta, (float) $job->longitude + $lonDelta])
            ->where('user_id', '!=', $job->client_id)
            ->whereHas('user', fn ($query) => $query->where('status', UserStatus::ACTIVE->value)->whereIn('role', [UserRole::PROFESSIONAL->value, UserRole::ADMIN->value]))
            ->whereHas('services', fn ($query) => $query->active()->where('category_id', $categoryId))
            ->whereDoesntHave('jobRequests', fn ($query) => $query->whereIn('status', array_map(fn (JobStatus $status) => $status->value, $activeJobStatuses)))
            ->whereDoesntHave('invitations', fn ($query) => $query->where('job_request_id', $job->getKey())->whereIn('status', [InvitationStatus::PENDING->value, InvitationStatus::VIEWED->value, InvitationStatus::ACCEPTED->value]));

        $candidates = $this->identityVerification->applyOperationalEligibility($candidates)->get();

        foreach ($candidates as $candidate) {
            $distance = $this->distance->distanceKm((float) $job->latitude, (float) $job->longitude, (float) $candidate->last_latitude, (float) $candidate->last_longitude);
            if ($distance > min($radius, (float) $candidate->service_radius_km)) {
                continue;
            }
            $invitation = $job->invitations()->firstOrNew(['professional_id' => $candidate->getKey()]);
            $invitation->forceFill([
                'professional_id' => $candidate->getKey(),
                'distance_km' => $distance,
                'status' => InvitationStatus::PENDING,
                'invited_at' => now(),
                'expires_at' => now()->addMinutes((int) config('chambapp.on_demand.invitation_timeout_minutes', 3)),
            ])->save();
            $candidate->user?->notify(new ChambappNotification(
                'on_demand_invitation',
                'Nueva chamba cerca de ti',
                ($job->category?->name ?? 'Servicio').' · a '.$distance.' km',
                route('professional.opportunities'),
            ));
        }
    }

    private function expireInvitations(JobRequest $job): void
    {
        $job->invitations()->open()->where('expires_at', '<', now())->update([
            'status' => InvitationStatus::EXPIRED,
            'responded_at' => now(),
        ]);
    }
}
