<?php

namespace App\Services;

use App\Enums\IdentityVerificationStatus;
use App\Enums\UserRole;
use App\Exceptions\IdentityVerificationRequiredException;
use App\Models\ProfessionalIdentityVerification;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProfessionalIdentityVerificationService
{
    public const REQUIRED_MESSAGE = 'Necesitas verificar tu identidad antes de aceptar trabajos.';

    public function isRequired(): bool
    {
        return (bool) config('chambapp.identity_verification.required', false);
    }

    public function hasCreatorExemption(ProfessionalProfile|User|null $target): bool
    {
        $user = $target instanceof User ? $target : $target?->user;
        if (! $user) {
            return false;
        }

        return $user->isCreator();
    }

    public function recordFor(ProfessionalProfile $professional): ProfessionalIdentityVerification
    {
        return $professional->identityVerification()->firstOrCreate([], [
            'status' => IdentityVerificationStatus::NOT_STARTED,
        ]);
    }

    public function statusFor(ProfessionalProfile $professional): IdentityVerificationStatus
    {
        if ($this->hasCreatorExemption($professional)) {
            return IdentityVerificationStatus::VERIFIED;
        }

        $record = $professional->relationLoaded('identityVerification')
            ? $professional->identityVerification
            : $professional->identityVerification()->first();

        if (! $record) {
            return IdentityVerificationStatus::NOT_STARTED;
        }

        if ($record->status === IdentityVerificationStatus::VERIFIED && $record->expires_at?->isPast()) {
            return IdentityVerificationStatus::EXPIRED;
        }

        return $record->status;
    }

    public function hasVerifiedIdentity(ProfessionalProfile $professional): bool
    {
        if ($this->hasCreatorExemption($professional)) {
            return true;
        }

        return $this->statusFor($professional) === IdentityVerificationStatus::VERIFIED;
    }

    public function professionalCanAcceptJobs(ProfessionalProfile|User|null $professional): bool
    {
        $user = $professional instanceof User ? $professional : $professional?->user;
        if ($user && ! $user->isActive()) {
            return false;
        }

        if (! $this->isRequired()) {
            return true;
        }

        $profile = $professional instanceof User ? $professional->professionalProfile : $professional;

        return $profile !== null && $this->hasVerifiedIdentity($profile);
    }

    public function ensureProfessionalCanAcceptJobs(ProfessionalProfile|User|null $professional): void
    {
        if (! $this->professionalCanAcceptJobs($professional)) {
            throw new IdentityVerificationRequiredException(self::REQUIRED_MESSAGE);
        }
    }

    public function applyOperationalEligibility(Builder $query): Builder
    {
        if (! $this->isRequired()) {
            return $query;
        }

        $creatorEmails = config('chambapp.creator_emails', ['gerawx@gmail.com', 'romy00120@gmail.com']);
        if (! is_array($creatorEmails)) {
            $creatorEmails = array_filter(array_map('trim', explode(',', (string) $creatorEmails)));
        }
        $creatorEmails = array_map('strtolower', (array) $creatorEmails);

        return $query->where(function (Builder $outerQuery) use ($creatorEmails): void {
            $outerQuery->whereHas('identityVerification', function (Builder $verification): void {
                $verification->where('status', IdentityVerificationStatus::VERIFIED->value)
                    ->where(function (Builder $expiry): void {
                        $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    });
            });

            if (! empty($creatorEmails)) {
                $outerQuery->orWhereHas('user', function (Builder $userQuery) use ($creatorEmails): void {
                    $userQuery->where('role', UserRole::ADMIN->value)
                        ->whereIn(DB::raw('LOWER(email)'), $creatorEmails);
                });
            }
        });
    }
}
