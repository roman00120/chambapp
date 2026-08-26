<?php

namespace App\Services;

use App\Exceptions\DiditException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class DiditClient
{
    /** @return array<string, mixed> */
    public function createSession(string $vendorData, string $callback, array $metadata = []): array
    {
        $this->assertConfigured();

        try {
            $response = $this->request()->post('/v3/session/', [
                'workflow_id' => (string) config('services.didit.workflow_id'),
                'vendor_data' => $vendorData,
                'callback' => $callback,
                'callback_method' => 'both',
                'metadata' => $metadata,
                'language' => 'es',
            ]);
        } catch (ConnectionException) {
            throw new DiditException('didit_connection_failed', 503, true);
        }

        $data = $this->validatedJson($response, [200, 201], 'didit_session_creation_failed');
        if (! is_string($data['session_id'] ?? null) || ! is_string($data['url'] ?? null)) {
            throw new DiditException('didit_invalid_session_response');
        }
        if (! $this->isSafeHostedUrl($data['url'])) {
            throw new DiditException('didit_unsafe_session_url');
        }

        return $data;
    }

    /** @return array<string, mixed> */
    public function decision(string $sessionId): array
    {
        $this->assertConfigured();

        try {
            $response = $this->request()->get('/v3/session/'.rawurlencode($sessionId).'/decision/');
        } catch (ConnectionException) {
            throw new DiditException('didit_connection_failed', 503, true);
        }

        return $this->validatedJson($response, [200], 'didit_decision_unavailable');
    }

    public function isConfigured(): bool
    {
        return filled(config('services.didit.api_url'))
            && filled(config('services.didit.api_key'))
            && filled(config('services.didit.workflow_id'));
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.didit.api_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->withHeaders(['x-api-key' => (string) config('services.didit.api_key')])
            ->timeout(max(1, (int) config('services.didit.timeout', 10)))
            ->connectTimeout(5);
    }

    private function assertConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new DiditException('didit_not_configured', 503);
        }
    }

    /**
     * @param  list<int>  $expectedStatuses
     * @return array<string, mixed>
     */
    private function validatedJson(Response $response, array $expectedStatuses, string $failureReason): array
    {
        if (! in_array($response->status(), $expectedStatuses, true)) {
            $status = $response->status();
            $reason = match ($status) {
                400, 422 => 'didit_invalid_request',
                401 => 'didit_invalid_api_key',
                403 => 'didit_forbidden',
                404 => 'didit_session_not_found',
                409 => 'didit_conflict',
                429 => 'didit_rate_limited',
                default => $failureReason,
            };

            throw new DiditException($reason, $status >= 500 || $status === 429 ? 503 : 422, $status === 429 || $status >= 500);
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new DiditException('didit_invalid_json_response');
        }

        return $data;
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
