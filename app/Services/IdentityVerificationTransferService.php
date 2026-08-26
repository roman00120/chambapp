<?php

namespace App\Services;

use App\Enums\IdentityVerificationStatus;
use App\Exceptions\IdentityVerificationTransferException;
use App\Models\IdentityVerificationTransfer;
use App\Models\ProfessionalIdentityVerification;
use App\Models\ProfessionalProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IdentityVerificationTransferService
{
    /** @return array{url: string, expires_at: Carbon} */
    public function issue(
        ProfessionalProfile $professional,
        ProfessionalIdentityVerification $verification,
        string $hostedUrl,
    ): array {
        if ($verification->professional_id !== $professional->getKey()
            || $verification->verification_provider !== 'didit'
            || blank($verification->provider_session_id)
            || ! $this->isSafeHostedUrl($hostedUrl)) {
            throw new IdentityVerificationTransferException('invalid_transfer_session', 422);
        }

        $token = Str::random(64);
        $expiresAt = now()->addMinutes(max(1, (int) config('chambapp.identity_verification.transfer_ttl_minutes', 10)));

        DB::transaction(function () use ($professional, $verification, $hostedUrl, $token, $expiresAt): void {
            IdentityVerificationTransfer::query()
                ->where('identity_verification_id', $verification->getKey())
                ->whereNull('consumed_at')
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            IdentityVerificationTransfer::query()->create([
                'professional_id' => $professional->getKey(),
                'identity_verification_id' => $verification->getKey(),
                'token_hash' => hash('sha256', $token),
                'provider_session_id' => $verification->provider_session_id,
                'hosted_url' => $hostedUrl,
                'expires_at' => $expiresAt,
            ]);
        });

        return [
            'url' => route('identity-verification.transfer', ['token' => $token]),
            'expires_at' => $expiresAt,
        ];
    }

    public function redeem(ProfessionalProfile $professional, string $token): string
    {
        if (! preg_match('/^[A-Za-z0-9]{64}$/', $token)) {
            throw new IdentityVerificationTransferException('transfer_not_found', 404);
        }

        return DB::transaction(function () use ($professional, $token): string {
            $transfer = IdentityVerificationTransfer::query()
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if (! $transfer || $transfer->professional_id !== $professional->getKey()) {
                throw new IdentityVerificationTransferException('transfer_not_found', 404);
            }
            if ($transfer->consumed_at || $transfer->revoked_at || $transfer->expires_at->isPast()) {
                throw new IdentityVerificationTransferException('transfer_unavailable', 410);
            }

            $verification = ProfessionalIdentityVerification::query()->lockForUpdate()->find($transfer->identity_verification_id);
            if (! $verification
                || $verification->professional_id !== $professional->getKey()
                || $verification->verification_provider !== 'didit'
                || ! hash_equals((string) $verification->provider_session_id, (string) $transfer->provider_session_id)
                || ! in_array($verification->status, [IdentityVerificationStatus::PENDING, IdentityVerificationStatus::NEEDS_REVIEW], true)
                || ! $this->isSafeHostedUrl((string) $transfer->hosted_url)) {
                throw new IdentityVerificationTransferException('transfer_session_mismatch', 410);
            }

            $transfer->forceFill(['consumed_at' => now()])->save();

            return (string) $transfer->hosted_url;
        });
    }

    private function isSafeHostedUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (($parts['scheme'] ?? null) !== 'https' || ! is_string($parts['host'] ?? null)) {
            return false;
        }

        $host = strtolower($parts['host']);

        return $host === 'didit.me' || str_ends_with($host, '.didit.me');
    }
}
