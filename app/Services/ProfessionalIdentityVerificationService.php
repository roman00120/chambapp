<?php

namespace App\Services;

use App\Enums\IdentityVerificationStatus;
use App\Exceptions\IdentityVerificationRequiredException;
use App\Models\ProfessionalIdentityVerification;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ProfessionalIdentityVerificationService
{
    public const REQUIRED_MESSAGE = 'Necesitas verificar tu identidad antes de aceptar trabajos.';

    public function isRequired(): bool
    {
        return (bool) config('chambapp.identity_verification.required', false);
    }

    public function recordFor(ProfessionalProfile $professional): ProfessionalIdentityVerification
    {
        return $professional->identityVerification()->firstOrCreate([], [
            'status' => IdentityVerificationStatus::NOT_STARTED,
        ]);
    }

    public function statusFor(ProfessionalProfile $professional): IdentityVerificationStatus
    {
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
        return $this->statusFor($professional) === IdentityVerificationStatus::VERIFIED;
    }

    public function professionalCanAcceptJobs(ProfessionalProfile|User|null $professional): bool
    {
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

        return $query->whereHas('identityVerification', function (Builder $verification): void {
            $verification->where('status', IdentityVerificationStatus::VERIFIED->value)
                ->where(function (Builder $expiry): void {
                    $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now());
                });
        });
    }
}
