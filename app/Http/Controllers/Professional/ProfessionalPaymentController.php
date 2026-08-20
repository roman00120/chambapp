<?php

namespace App\Http\Controllers\Professional;

use App\Enums\VerificationStatus;
use App\Exceptions\MercadoPagoException;
use App\Http\Controllers\Controller;
use App\Models\ProfessionalProfile;
use App\Services\MercadoPagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalPaymentController extends Controller
{
    public function show(Request $request): View
    {
        $profile = $this->profileFor($request);

        return view('professional.payments.settings', [
            'profile' => $profile,
            'providerConfigured' => filled(config('services.mercadopago.client_id')) && filled(config('services.mercadopago.client_secret')),
        ]);
    }

    public function connect(Request $request, MercadoPagoService $mercadoPago): RedirectResponse
    {
        if (! filled(config('services.mercadopago.client_id')) || ! filled(config('services.mercadopago.client_secret'))) {
            return back()->withErrors(['payment' => 'Mercado Pago todavía no está configurado en el servidor.']);
        }

        $state = bin2hex(random_bytes(32));
        $request->session()->put('mercadopago.oauth', [
            'state' => $state,
            'user_id' => $request->user()->getKey(),
        ]);

        return redirect()->away($mercadoPago->authorizationUrl($state));
    }

    public function callback(Request $request, MercadoPagoService $mercadoPago): RedirectResponse
    {
        $oauth = $request->session()->pull('mercadopago.oauth');
        $receivedState = (string) $request->query('state');
        if (! is_array($oauth) || ! hash_equals((string) ($oauth['state'] ?? ''), $receivedState) || (int) ($oauth['user_id'] ?? 0) !== $request->user()->getKey()) {
            return redirect()->route('professional.payments.settings')->withErrors(['payment' => 'No pudimos validar la conexión con Mercado Pago.']);
        }
        if (! filled($request->query('code'))) {
            return redirect()->route('professional.payments.settings')->withErrors(['payment' => 'La conexión con Mercado Pago fue cancelada.']);
        }

        try {
            $credentials = $mercadoPago->exchangeAuthorizationCode((string) $request->query('code'), $receivedState);
            $profile = $this->profileFor($request);
            $profile->forceFill([
                'mercadopago_user_id' => (string) data_get($credentials, 'user_id'),
                'mercadopago_access_token' => (string) data_get($credentials, 'access_token'),
                'mercadopago_refresh_token' => (string) data_get($credentials, 'refresh_token'),
                'mercadopago_public_key' => data_get($credentials, 'public_key'),
                'mercadopago_token_expires_at' => now()->addSeconds((int) data_get($credentials, 'expires_in', 0)),
                'mercadopago_connected_at' => now(),
            ])->save();
        } catch (MercadoPagoException) {
            return redirect()->route('professional.payments.settings')->withErrors(['payment' => 'No pudimos completar la conexión con Mercado Pago.']);
        }

        return redirect()->route('professional.payments.settings')->with('status', 'Mercado Pago conectado correctamente.');
    }

    private function profileFor(Request $request): ProfessionalProfile
    {
        return $request->user()->professionalProfile()->firstOrCreate([
            'user_id' => $request->user()->getKey(),
        ], [
            'verification_status' => VerificationStatus::UNVERIFIED,
        ]);
    }
}
