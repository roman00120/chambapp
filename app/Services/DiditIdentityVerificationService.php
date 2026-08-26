<?php

namespace App\Services;

use App\Enums\IdentityVerificationStatus;
use App\Exceptions\DiditException;
use App\Models\DiditWebhookEvent;
use App\Models\ProfessionalIdentityVerification;
use App\Models\ProfessionalProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DiditIdentityVerificationService
{
    public const PURPOSE = 'professional_identity_verification';

    public function __construct(
        private readonly DiditClient $client,
        private readonly ProfessionalIdentityVerificationService $identityVerification,
    ) {}

    public function isConfigured(): bool
    {
        return config('chambapp.identity_verification.provider') === 'didit'
            && $this->client->isConfigured();
    }

    public function canStart(ProfessionalProfile $professional): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        return $this->identityVerification->statusFor($professional) !== IdentityVerificationStatus::VERIFIED;
    }

    /**
     * @return array{verification: ProfessionalIdentityVerification, url: string}
     */
    public function start(
        ProfessionalProfile $professional,
        ?string $ipAddress,
        ?string $userAgent,
        ?string $callback = null,
    ): array {
        if (! $this->canStart($professional)) {
            throw new DiditException(
                $this->isConfigured() ? 'identity_already_verified' : 'didit_not_configured',
                422,
            );
        }

        $record = $this->identityVerification->recordFor($professional);
        $this->recordConsent($record, $professional, $ipAddress, $userAgent);

        $response = $this->client->createSession(
            $this->vendorDataFor($professional),
            $callback ?? route('professional.identity-verification.callback'),
            ['purpose' => self::PURPOSE],
        );

        $sessionId = (string) $response['session_id'];
        $status = $this->mapStatus((string) ($response['status'] ?? 'Not Started'));

        $record = DB::transaction(function () use ($record, $sessionId, $status, $response): ProfessionalIdentityVerification {
            $locked = ProfessionalIdentityVerification::query()->lockForUpdate()->findOrFail($record->id);
            if (filled($locked->provider_session_id) && ! hash_equals((string) $locked->provider_session_id, $sessionId)) {
                if (in_array($locked->status, [IdentityVerificationStatus::PENDING, IdentityVerificationStatus::NEEDS_REVIEW], true)
                    && ! $locked->expires_at?->isPast()) {
                    throw new DiditException('identity_session_already_active', 409);
                }
            }

            $previousStatus = $locked->status;
            $locked->forceFill([
                'verification_provider' => 'didit',
                'provider_verification_id' => $sessionId,
                'provider_session_id' => $sessionId,
                'provider_status' => (string) ($response['status'] ?? 'Not Started'),
                'status' => $status,
                'started_at' => $locked->started_at ?? now(),
                'submitted_at' => $status === IdentityVerificationStatus::PENDING
                    ? ($locked->submitted_at ?? now())
                    : $locked->submitted_at,
                'last_provider_sync_at' => now(),
                'verified_at' => null,
                'reason_code' => null,
                'rejected_at' => null,
                'expires_at' => null,
            ])->save();

            $this->recordStatusEvent($locked, $previousStatus, $status, 'start');

            return $locked;
        });

        return ['verification' => $record, 'url' => (string) $response['url']];
    }

    public function sync(ProfessionalProfile $professional): ProfessionalIdentityVerification
    {
        $record = $this->identityVerification->recordFor($professional);
        if (! filled($record->provider_session_id) || $record->verification_provider !== 'didit') {
            throw new DiditException('identity_session_not_started', 422);
        }

        return $this->syncRecord($record);
    }

    public function syncRecord(ProfessionalIdentityVerification $record): ProfessionalIdentityVerification
    {
        $record->loadMissing('professional');
        $sessionId = (string) $record->provider_session_id;
        $decision = $this->client->decision($sessionId);

        if (! is_string($decision['session_id'] ?? null)
            || ! hash_equals($sessionId, (string) $decision['session_id'])) {
            throw new DiditException('didit_session_mismatch');
        }

        if (($decision['session_kind'] ?? null) !== 'user') {
            throw new DiditException('didit_session_kind_mismatch');
        }

        $expectedVendorData = $this->vendorDataFor($record->professional);
        if (! is_string($decision['vendor_data'] ?? null)
            || ! hash_equals($expectedVendorData, (string) $decision['vendor_data'])) {
            throw new DiditException('didit_vendor_data_mismatch');
        }

        $configuredWorkflow = (string) config('services.didit.workflow_id');
        if (! is_string($decision['workflow_id'] ?? null)
            || ! hash_equals($configuredWorkflow, (string) $decision['workflow_id'])) {
            throw new DiditException('didit_workflow_mismatch');
        }

        return $this->applyDecision($record, $decision);
    }

    public function processWebhookEvent(DiditWebhookEvent $event): void
    {
        if ($event->processing_status === 'processed') {
            return;
        }

        $record = ProfessionalIdentityVerification::query()
            ->where('provider_session_id', $event->provider_session_id)
            ->first();

        if (! $record) {
            $event->forceFill([
                'processing_status' => 'ignored',
                'failure_code' => 'session_not_found',
                'processed_at' => now(),
            ])->save();

            return;
        }

        try {
            $this->syncRecord($record);
            $event->forceFill([
                'processing_status' => 'processed',
                'failure_code' => null,
                'processed_at' => now(),
            ])->save();
        } catch (DiditException $exception) {
            $event->forceFill([
                'processing_status' => $exception->retryable ? 'retryable' : 'failed',
                'failure_code' => $exception->reason,
                'processed_at' => now(),
            ])->save();

            throw $exception;
        }
    }

    public function mapStatus(string $providerStatus): IdentityVerificationStatus
    {
        return match (strtolower(trim($providerStatus))) {
            'approved' => IdentityVerificationStatus::VERIFIED,
            'declined', 'abandoned' => IdentityVerificationStatus::REJECTED,
            'in review' => IdentityVerificationStatus::NEEDS_REVIEW,
            'expired', 'kyc expired' => IdentityVerificationStatus::EXPIRED,
            'not started', 'in progress', 'awaiting user', 'resubmitted' => IdentityVerificationStatus::PENDING,
            default => IdentityVerificationStatus::NEEDS_REVIEW,
        };
    }

    public function vendorDataFor(ProfessionalProfile $professional): string
    {
        $key = (string) config('app.key');

        return 'chambapp-professional-'.hash_hmac('sha256', (string) $professional->getKey(), $key);
    }

    /** @param array<string, mixed> $decision */
    private function applyDecision(ProfessionalIdentityVerification $record, array $decision): ProfessionalIdentityVerification
    {
        $providerStatus = (string) ($decision['status'] ?? 'Unknown');
        $status = $this->mapStatus($providerStatus);

        return DB::transaction(function () use ($record, $decision, $providerStatus, $status): ProfessionalIdentityVerification {
            $locked = ProfessionalIdentityVerification::query()->lockForUpdate()->findOrFail($record->id);
            if (! hash_equals((string) $locked->provider_session_id, (string) $decision['session_id'])) {
                throw new DiditException('didit_stale_session');
            }

            $previousStatus = $locked->status;
            if ($previousStatus === IdentityVerificationStatus::VERIFIED
                && ! in_array($status, [IdentityVerificationStatus::VERIFIED, IdentityVerificationStatus::EXPIRED], true)) {
                $status = IdentityVerificationStatus::VERIFIED;
            }

            $reasonCode = match (strtolower(trim($providerStatus))) {
                'declined' => 'didit_declined',
                'abandoned' => 'didit_abandoned',
                'expired', 'kyc expired' => 'didit_expired',
                'in review' => 'didit_in_review',
                default => null,
            };

            $locked->forceFill([
                'provider_status' => mb_substr($providerStatus, 0, 50),
                'status' => $status,
                'verification_level' => $status === IdentityVerificationStatus::VERIFIED
                    ? 'document_face_liveness'
                    : $locked->verification_level,
                'document_type' => $status === IdentityVerificationStatus::VERIFIED
                    ? 'official_document'
                    : $locked->document_type,
                'submitted_at' => $locked->submitted_at ?? now(),
                'verified_at' => $status === IdentityVerificationStatus::VERIFIED
                    ? ($locked->verified_at ?? now())
                    : null,
                'rejected_at' => $status === IdentityVerificationStatus::REJECTED
                    ? ($locked->rejected_at ?? now())
                    : null,
                'expires_at' => $this->parseDate($decision['expires_at'] ?? null) ?? $locked->expires_at,
                'reason_code' => $reasonCode,
                'last_provider_sync_at' => now(),
            ])->save();

            $this->recordStatusEvent($locked, $previousStatus, $status, 'didit_api', $reasonCode);

            return $locked->fresh();
        });
    }

    private function recordConsent(
        ProfessionalIdentityVerification $record,
        ProfessionalProfile $professional,
        ?string $ipAddress,
        ?string $userAgent,
    ): void {
        $record->consents()->create([
            'professional_id' => $professional->getKey(),
            'consent_version' => (string) config('chambapp.identity_verification.consent_version'),
            'privacy_notice_version' => (string) config('chambapp.identity_verification.privacy_notice_version'),
            'purpose' => self::PURPOSE,
            'accepted_at' => now(),
            'ip_hash' => filled($ipAddress) ? hash('sha256', (string) $ipAddress) : null,
            'user_agent_hash' => filled($userAgent) ? hash('sha256', (string) $userAgent) : null,
        ]);
    }

    private function recordStatusEvent(
        ProfessionalIdentityVerification $record,
        IdentityVerificationStatus $from,
        IdentityVerificationStatus $to,
        string $source,
        ?string $reasonCode = null,
    ): void {
        if ($from === $to && $source !== 'start') {
            return;
        }

        $record->events()->create([
            'provider_session_id' => $record->provider_session_id,
            'source' => $source,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'reason_code' => $reasonCode,
            'occurred_at' => now(),
        ]);
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
