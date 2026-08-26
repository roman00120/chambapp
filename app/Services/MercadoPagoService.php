<?php

namespace App\Services;

use App\Exceptions\MercadoPagoException;
use App\Models\CommerceOrder;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MercadoPagoService
{
    public function authorizationUrl(string $state): string
    {
        $query = http_build_query([
            'client_id' => config('services.mercadopago.client_id'),
            'response_type' => 'code',
            'platform_id' => 'mp',
            'scope' => 'offline_access payments write',
            'redirect_uri' => route('professional.payments.oauth-callback'),
            'state' => $state,
        ]);

        return rtrim((string) config('services.mercadopago.auth_url'), '?').'?'.$query;
    }

    public function exchangeAuthorizationCode(string $code, string $state): array
    {
        return $this->oauthRequest([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => route('professional.payments.oauth-callback'),
            'state' => $state,
        ]);
    }

    public function createPreference(Payment $payment): array
    {
        $payment->loadMissing(['jobRequest.client', 'jobRequest.service', 'professional.user']);
        if ($payment->jobRequest && $payment->jobRequest->client_id === $payment->professional?->user_id) {
            throw new MercadoPagoException('No está permitido realizar pagos entre la misma cuenta de cliente y profesional.');
        }
        if ($payment->professional?->user?->email && $payment->jobRequest?->client?->email && strtolower((string) $payment->professional->user->email) === strtolower((string) $payment->jobRequest->client->email)) {
            throw new MercadoPagoException('El comprador y el vendedor no pueden compartir la misma cuenta en Mercado Pago.');
        }
        $token = $this->sellerToken($payment->professional);
        $expiration = $this->preferenceExpiration();
        $payload = [
            'items' => [[
                'id' => 'job-'.$payment->job_request_id,
                'title' => Str::limit((string) ($payment->jobRequest->service?->title ?? $payment->jobRequest->title), 120),
                'quantity' => 1,
                'currency_id' => $payment->currency,
                'unit_price' => (float) ($payment->customer_total ?? $payment->gross_amount),
            ]],
            'marketplace_fee' => (float) ($payment->platform_gross_fee ?? $payment->platform_fee),
            'external_reference' => $payment->external_reference,
            'back_urls' => [
                'success' => route('payments.return.success'),
                'pending' => route('payments.return.pending'),
                'failure' => route('payments.return.error'),
            ],
            'auto_return' => 'approved',
            'notification_url' => route('webhooks.mercadopago'),
            'expires' => true,
            'expiration_date_from' => $expiration['from'],
            'expiration_date_to' => $expiration['to'],
        ];

        $response = $this->postOnce($this->apiRequest($token), $this->apiUrl('/checkout/preferences'), $payload, 'checkout_preference');
        $data = $this->responseData($response);
        if (! $response->successful() || ! filled(data_get($data, 'id')) || ! filled(data_get($data, 'init_point'))) {
            throw new MercadoPagoException('Mercado Pago no pudo crear la preferencia.');
        }

        return [
            'id' => (string) data_get($data, 'id'),
            'url' => (string) ($this->isProduction() ? data_get($data, 'init_point') : (data_get($data, 'sandbox_init_point') ?: data_get($data, 'init_point'))),
            'expires_at' => $expiration['expires_at'],
        ];
    }

    public function createPlatformPreference(string $title, string $amount, string $externalReference): array
    {
        $token = (string) config('services.mercadopago.access_token');
        if ($token === '' || ! filled(config('services.mercadopago.user_id'))) {
            throw new MercadoPagoException('Mercado Pago de plataforma no está configurado completamente.');
        }
        $expiration = $this->preferenceExpiration();

        $response = $this->postOnce($this->apiRequest($token), $this->apiUrl('/checkout/preferences'), [
            'items' => [[
                'id' => $externalReference,
                'title' => Str::limit($title, 120),
                'quantity' => 1,
                'currency_id' => config('chambapp.payments.currency', 'MXN'),
                'unit_price' => (float) $amount,
            ]],
            'external_reference' => $externalReference,
            'back_urls' => [
                'success' => route('commerce.return.success'),
                'pending' => route('commerce.return.pending'),
                'failure' => route('commerce.return.error'),
            ],
            'auto_return' => 'approved',
            'notification_url' => route('webhooks.mercadopago'),
            'expires' => true,
            'expiration_date_from' => $expiration['from'],
            'expiration_date_to' => $expiration['to'],
        ]);

        $data = $this->responseData($response);
        if (! $response->successful() || ! filled(data_get($data, 'id')) || ! filled(data_get($data, 'init_point'))) {
            throw new MercadoPagoException('Mercado Pago no pudo crear la compra.');
        }

        return [
            'id' => (string) data_get($data, 'id'),
            'url' => (string) ($this->isProduction() ? data_get($data, 'init_point') : (data_get($data, 'sandbox_init_point') ?: data_get($data, 'init_point'))),
            'expires_at' => $expiration['expires_at'],
        ];
    }

    public function getPayment(string $providerPaymentId, ProfessionalProfile $professional): array
    {
        $response = $this->getWithRetry($this->sellerToken($professional), $this->apiUrl('/v1/payments/'.rawurlencode($providerPaymentId)));
        $data = $this->responseData($response);
        if (! $response->successful() || $data === null) {
            throw new MercadoPagoException('Mercado Pago no pudo consultar el pago.');
        }

        return $data;
    }

    public function getPlatformPayment(string $providerPaymentId): array
    {
        $token = (string) config('services.mercadopago.access_token');
        if ($token === '') {
            throw new MercadoPagoException('Mercado Pago de plataforma no está configurado.');
        }
        $response = $this->getWithRetry($token, $this->apiUrl('/v1/payments/'.rawurlencode($providerPaymentId)));
        $data = $this->responseData($response);
        if (! $response->successful() || $data === null) {
            throw new MercadoPagoException('Mercado Pago no pudo consultar el pago.');
        }

        return $data;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchPayments(Payment $payment): array
    {
        $payment->loadMissing('professional');
        $token = $this->sellerToken($payment->professional);
        $results = [];
        $offset = 0;
        $limit = 30;

        do {
            $pageLimit = min($limit, 100 - $offset);
            $response = $this->getWithRetry($token, $this->apiUrl('/v1/payments/search'), [
                'external_reference' => $payment->external_reference,
                'sort' => 'date_created',
                'criteria' => 'desc',
                'range' => 'date_created',
                'begin_date' => 'NOW-30DAYS',
                'end_date' => 'NOW',
                'offset' => $offset,
                'limit' => $pageLimit,
            ]);
            $data = $this->responseData($response);

            if (! $response->successful() || ! is_array(data_get($data, 'results'))) {
                throw new MercadoPagoException('Mercado Pago no pudo reconciliar el pago.');
            }

            $page = array_values(array_filter(
                data_get($data, 'results'),
                static fn (mixed $result): bool => is_array($result),
            ));
            array_push($results, ...$page);
            $offset += count($page);
            $total = (int) data_get($data, 'paging.total', count($results));
        } while ($page !== [] && $offset < min($total, 100));

        return $results;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchPlatformPayments(CommerceOrder $order): array
    {
        $token = (string) config('services.mercadopago.access_token');
        if ($token === '') {
            throw new MercadoPagoException('Mercado Pago de plataforma no está configurado.');
        }
        $results = [];
        $offset = 0;
        $limit = 30;

        do {
            $pageLimit = min($limit, 100 - $offset);
            $response = $this->getWithRetry($token, $this->apiUrl('/v1/payments/search'), [
                'external_reference' => $order->external_reference,
                'sort' => 'date_created',
                'criteria' => 'desc',
                'range' => 'date_created',
                'begin_date' => 'NOW-30DAYS',
                'end_date' => 'NOW',
                'offset' => $offset,
                'limit' => $pageLimit,
            ]);
            $data = $this->responseData($response);

            if (! $response->successful() || ! is_array(data_get($data, 'results'))) {
                throw new MercadoPagoException('Mercado Pago no pudo reconciliar la compra.');
            }

            $page = array_values(array_filter(
                data_get($data, 'results'),
                static fn (mixed $result): bool => is_array($result),
            ));
            array_push($results, ...$page);
            $offset += count($page);
            $total = (int) data_get($data, 'paging.total', count($results));
        } while ($page !== [] && $offset < min($total, 100));

        return $results;
    }

    public function refreshAccessToken(ProfessionalProfile $professional): string
    {
        return Cache::lock('mercadopago-token-refresh:'.$professional->getKey(), 30)
            ->block(10, function () use ($professional): string {
                $fresh = ProfessionalProfile::query()->findOrFail($professional->getKey());
                if ($fresh->mercadopago_token_expires_at?->gt(now()->addDays(7))) {
                    return (string) $fresh->mercadopago_access_token;
                }

                $credentials = $this->oauthRequest([
                    'grant_type' => 'refresh_token',
                    'refresh_token' => (string) $fresh->mercadopago_refresh_token,
                ]);
                $fresh->forceFill([
                    'mercadopago_access_token' => (string) data_get($credentials, 'access_token'),
                    'mercadopago_refresh_token' => (string) data_get($credentials, 'refresh_token', $fresh->mercadopago_refresh_token),
                    'mercadopago_public_key' => data_get($credentials, 'public_key', $fresh->mercadopago_public_key),
                    'mercadopago_token_expires_at' => now()->addSeconds((int) data_get($credentials, 'expires_in', 0)),
                ])->save();

                return (string) $fresh->mercadopago_access_token;
            });
    }

    private function oauthRequest(array $payload): array
    {
        $response = $this->postOnce(Http::asForm()
            ->acceptJson()
            ->connectTimeout(min(5, (int) config('chambapp.payments.checkout_timeout', 10)))
            ->timeout((int) config('chambapp.payments.checkout_timeout', 10)), $this->apiUrl('/oauth/token'), array_merge([
                'client_id' => config('services.mercadopago.client_id'),
                'client_secret' => config('services.mercadopago.client_secret'),
            ], $payload), 'oauth_token');

        $data = $this->responseData($response);
        if (! $response->successful() || ! filled(data_get($data, 'access_token'))) {
            throw new MercadoPagoException('No fue posible conectar Mercado Pago.');
        }

        return $data;
    }

    private function apiRequest(string $token): PendingRequest
    {
        return Http::acceptJson()
            ->withToken($token)
            ->connectTimeout(min(5, (int) config('chambapp.payments.checkout_timeout', 10)))
            ->timeout((int) config('chambapp.payments.checkout_timeout', 10));
    }

    /**
     * GETs are safe to retry: they only read provider state. POST operations are deliberately
     * sent once because Checkout Pro Preferences and OAuth do not document an idempotency key.
     * An ambiguous POST outcome is surfaced to the caller rather than risking a duplicate resource.
     *
     * @param array<string, mixed> $query
     */
    private function getWithRetry(string $token, string $url, array $query = []): Response
    {
        try {
            return $this->apiRequest($token)
                ->retry(
                    max(1, (int) config('chambapp.payments.read_retry_attempts', 3)),
                    fn (int $attempt, Throwable $exception): int => $this->retryDelayMilliseconds($attempt, $exception),
                    function (Throwable $exception): bool {
                        $status = $exception instanceof RequestException ? $exception->response->status() : null;
                        $retryable = $exception instanceof ConnectionException || in_array($status, [429, 500, 502, 503, 504], true);
                        if ($retryable) {
                            Log::warning('Retrying safe Mercado Pago read request', ['http_status' => $status]);
                        }

                        return $retryable;
                    },
                )
                ->throw()
                ->get($url, $query);
        } catch (ConnectionException|RequestException $exception) {
            throw new MercadoPagoException('Mercado Pago no pudo completar una consulta segura.', previous: $exception);
        }
    }

    /** @param array<string, mixed> $payload */
    private function postOnce(PendingRequest $request, string $url, array $payload, string $operation): Response
    {
        try {
            return $request->post($url, $payload);
        } catch (ConnectionException $exception) {
            Log::warning('Mercado Pago write request failed without automatic retry', ['operation' => $operation]);

            throw new MercadoPagoException('Mercado Pago no pudo completar la operaciÃ³n. No se reintentÃ³ automÃ¡ticamente.', previous: $exception);
        }
    }

    private function retryDelayMilliseconds(int $attempt, Throwable $exception): int
    {
        $retryAfter = $exception instanceof RequestException ? $exception->response->header('Retry-After') : null;
        if (is_string($retryAfter) && ctype_digit($retryAfter)) {
            return min(10000, max(0, ((int) $retryAfter) * 1000));
        }

        $base = max(0, (int) config('chambapp.payments.read_retry_base_milliseconds', 250));
        $cap = max($base, (int) config('chambapp.payments.read_retry_max_milliseconds', 2000));
        $backoff = min($cap, $base * (2 ** max(0, $attempt - 1)));

        return $backoff === 0 ? 0 : min($cap, $backoff + random_int(0, max(1, intdiv($backoff, 4))));
    }

    /** @return array<string, mixed>|null */
    private function responseData(Response $response): ?array
    {
        $data = $response->json(null, null, JSON_BIGINT_AS_STRING);

        return is_array($data) ? $data : null;
    }

    private function sellerToken(ProfessionalProfile $professional): string
    {
        if (! $professional->isMercadoPagoConnected()) {
            throw new MercadoPagoException('El profesional no tiene Mercado Pago conectado.');
        }

        if ($professional->mercadopago_token_expires_at?->lte(now()->addDays(7)) && filled($professional->mercadopago_refresh_token)) {
            return $this->refreshAccessToken($professional);
        }

        return (string) $professional->mercadopago_access_token;
    }

    private function apiUrl(string $path): string
    {
        return rtrim((string) config('services.mercadopago.api_url'), '/').$path;
    }

    /** @return array{from: string, to: string, expires_at: \DateTimeInterface} */
    private function preferenceExpiration(): array
    {
        $now = now();
        $expiresAt = $now->copy()->addHours(max(1, (int) config('chambapp.payments.preference_lifetime_hours', 24)));

        return [
            'from' => $now->copy()->subMinute()->format('Y-m-d\TH:i:s.vP'),
            'to' => $expiresAt->format('Y-m-d\TH:i:s.vP'),
            'expires_at' => $expiresAt,
        ];
    }

    private function isProduction(): bool
    {
        return app()->environment('production');
    }
}
