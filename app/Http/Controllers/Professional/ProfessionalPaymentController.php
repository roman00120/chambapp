<?php

namespace App\Http\Controllers\Professional;

use App\Enums\VerificationStatus;
use App\Exceptions\MercadoPagoException;
use App\Http\Controllers\Controller;
use App\Models\ProfessionalProfile;
use App\Services\MercadoPagoService;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'issued_at' => now()->timestamp,
        ]);

        return redirect()->away($mercadoPago->authorizationUrl($state));
    }

    public function callback(Request $request, MercadoPagoService $mercadoPago): RedirectResponse
    {
        $oauth = $request->session()->pull('mercadopago.oauth');
        $receivedState = (string) $request->query('state');
        $stateLifetime = max(60, (int) config('chambapp.payments.oauth_state_lifetime_seconds', 600));
        $issuedAt = is_array($oauth) ? filter_var($oauth['issued_at'] ?? null, FILTER_VALIDATE_INT) : false;
        $stateExpired = $issuedAt === false || $issuedAt < now()->subSeconds($stateLifetime)->timestamp;
        if (! is_array($oauth) || $stateExpired || ! hash_equals((string) ($oauth['state'] ?? ''), $receivedState) || (int) ($oauth['user_id'] ?? 0) !== $request->user()->getKey()) {
            return redirect()->route('professional.payments.settings')->withErrors(['payment' => 'No pudimos validar la conexión con Mercado Pago.']);
        }
        if (! filled($request->query('code'))) {
            return redirect()->route('professional.payments.settings')->withErrors(['payment' => 'La conexión con Mercado Pago fue cancelada.']);
        }

        try {
            $credentials = $mercadoPago->exchangeAuthorizationCode((string) $request->query('code'), $receivedState);
            $sellerId = (string) data_get($credentials, 'user_id');
            if ($sellerId === '') {
                throw new MercadoPagoException('Mercado Pago no devolvió una cuenta de vendedor.');
            }
            DB::transaction(function () use ($request, $credentials, $sellerId): void {
                $profile = ProfessionalProfile::query()
                    ->lockForUpdate()
                    ->findOrFail($this->profileFor($request)->getKey());
                $sellerBelongsToAnotherProfile = ProfessionalProfile::query()
                    ->where('mercadopago_user_id', $sellerId)
                    ->where('id', '!=', $profile->getKey())
                    ->exists();
                if ($sellerBelongsToAnotherProfile) {
                    throw new DomainException('Esta cuenta de Mercado Pago ya está vinculada a otro profesional.');
                }
                $profile->forceFill([
                    'mercadopago_user_id' => $sellerId,
                    'mercadopago_access_token' => (string) data_get($credentials, 'access_token'),
                    'mercadopago_refresh_token' => (string) data_get($credentials, 'refresh_token'),
                    'mercadopago_public_key' => data_get($credentials, 'public_key'),
                    'mercadopago_token_expires_at' => now()->addSeconds((int) data_get($credentials, 'expires_in', 0)),
                    'mercadopago_connected_at' => now(),
                ])->save();
            });
        } catch (MercadoPagoException) {
            return redirect()->route('professional.payments.settings')->withErrors(['payment' => 'No pudimos completar la conexión con Mercado Pago.']);
        } catch (DomainException $exception) {
            return redirect()->route('professional.payments.settings')->withErrors(['payment' => $exception->getMessage()]);
        } catch (QueryException $exception) {
            report($exception);

            return redirect()->route('professional.payments.settings')->withErrors(['payment' => 'La cuenta de Mercado Pago ya está vinculada o no pudo guardarse de forma segura.']);
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
