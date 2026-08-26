<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ProfessionalProfile;
use App\Services\DiditIdentityVerificationService;
use App\Services\ProfessionalIdentityVerificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfessionalIdentityVerificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ProfessionalProfile $profile */
        $profile = $this->resource;
        $service = app(ProfessionalIdentityVerificationService::class);
        $didit = app(DiditIdentityVerificationService::class);
        $record = $service->recordFor($profile);
        $status = $service->statusFor($profile->setRelation('identityVerification', $record));

        return [
            'status' => $status->value,
            'is_required' => $service->isRequired(),
            'identity_verified' => $service->hasVerifiedIdentity($profile),
            'can_accept_jobs' => $service->professionalCanAcceptJobs($profile),
            'can_start_verification' => $didit->canStart($profile),
            'provider' => $record->verification_provider,
            'verification_level' => $record->verification_level,
            'document_type' => $record->document_type,
            'submitted_at' => $record->submitted_at?->toIso8601String(),
            'verified_at' => $record->verified_at?->toIso8601String(),
            'rejected_at' => $record->rejected_at?->toIso8601String(),
            'expires_at' => $record->expires_at?->toIso8601String(),
            'reason_code' => $record->reason_code,
            'last_provider_sync_at' => $record->last_provider_sync_at?->toIso8601String(),
            'documents_stored_by_chambapp' => false,
            'message' => $service->isRequired() && ! $service->hasVerifiedIdentity($profile)
                ? ProfessionalIdentityVerificationService::REQUIRED_MESSAGE
                : null,
        ];
    }
}
