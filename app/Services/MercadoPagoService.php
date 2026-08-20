<?php

namespace App\Services;

use App\Exceptions\MercadoPagoException;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
        $payment->loadMissing(['jobRequest.service', 'professional']);
        $token = $this->sellerToken($payment->professional);
        $payload = [
            'items' => [[
                'id' => 'job-'.$payment->job_request_id,
                'title' => Str::limit((string) ($payment->jobRequest->service?->title ?? $payment->jobRequest->title), 120),
                'quantity' => 1,
                'currency_id' => $payment->currency,
                'unit_price' => (string) $payment->gross_amount,
            ]],
            'marketplace_fee' => (string) $payment->platform_fee,
            'external_reference' => $payment->external_reference,
            'back_urls' => [
                'success' => route('payments.return.success'),
                'pending' => route('payments.return.pending'),
                'failure' => route('payments.return.error'),
            ],
            'auto_return' => 'approved',
            'notification_url' => route('webhooks.mercadopago'),
        ];

        $response = $this->apiRequest($token)->post($this->apiUrl('/checkout/preferences'), $payload);
        if (! $response->successful() || ! filled($response->json('id')) || ! filled($response->json('init_point'))) {
            throw new MercadoPagoException('Mercado Pago no pudo crear la preferencia.');
        }

        return [
            'id' => (string) $response->json('id'),
            'url' => (string) ($this->isProduction() ? $response->json('init_point') : ($response->json('sandbox_init_point') ?: $response->json('init_point'))),
        ];
    }

    public function getPayment(string $providerPaymentId, ProfessionalProfile $professional): array
    {
        $response = $this->apiRequest($this->sellerToken($professional))->get($this->apiUrl('/v1/payments/'.rawurlencode($providerPaymentId)));
        if (! $response->successful() || ! is_array($response->json())) {
            throw new MercadoPagoException('Mercado Pago no pudo consultar el pago.');
        }

        return $response->json();
    }

    public function refreshAccessToken(ProfessionalProfile $professional): string
    {
        $credentials = $this->oauthRequest([
            'grant_type' => 'refresh_token',
            'refresh_token' => (string) $professional->mercadopago_refresh_token,
        ]);
        $professional->forceFill([
            'mercadopago_access_token' => (string) data_get($credentials, 'access_token'),
            'mercadopago_refresh_token' => (string) data_get($credentials, 'refresh_token', $professional->mercadopago_refresh_token),
            'mercadopago_public_key' => data_get($credentials, 'public_key', $professional->mercadopago_public_key),
            'mercadopago_token_expires_at' => now()->addSeconds((int) data_get($credentials, 'expires_in', 0)),
        ])->save();

        return (string) $professional->mercadopago_access_token;
    }

    private function oauthRequest(array $payload): array
    {
        $response = Http::asForm()
            ->acceptJson()
            ->timeout((int) config('chambapp.payments.checkout_timeout', 10))
            ->post($this->apiUrl('/oauth/token'), array_merge([
                'client_id' => config('services.mercadopago.client_id'),
                'client_secret' => config('services.mercadopago.client_secret'),
            ], $payload));

        if (! $response->successful() || ! filled($response->json('access_token'))) {
            throw new MercadoPagoException('No fue posible conectar Mercado Pago.');
        }

        return $response->json();
    }

    private function apiRequest(string $token): PendingRequest
    {
        return Http::acceptJson()
            ->withToken($token)
            ->timeout((int) config('chambapp.payments.checkout_timeout', 10));
    }

    private function sellerToken(ProfessionalProfile $professional): string
    {
        if (! $professional->isMercadoPagoConnected()) {
            throw new MercadoPagoException('El profesional no tiene Mercado Pago conectado.');
        }

        if ($professional->mercadopago_token_expires_at?->isPast() && filled($professional->mercadopago_refresh_token)) {
            return $this->refreshAccessToken($professional);
        }

        return (string) $professional->mercadopago_access_token;
    }

    private function apiUrl(string $path): string
    {
        return rtrim((string) config('services.mercadopago.api_url'), '/').$path;
    }

    private function isProduction(): bool
    {
        return app()->environment('production');
    }
}
