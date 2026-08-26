<?php

namespace App\Http\Controllers\Professional;

use App\Exceptions\DiditException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StartIdentityVerificationRequest;
use App\Services\DiditIdentityVerificationService;
use App\Services\ProfessionalIdentityVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProfessionalIdentityVerificationController extends Controller
{
    public function __invoke(
        Request $request,
        ProfessionalIdentityVerificationService $service,
        DiditIdentityVerificationService $didit,
    ): View {
        $profile = $request->user()->professionalProfile()->firstOrFail();
        $record = $service->recordFor($profile);

        return view('professional.identity-verification.show', [
            'record' => $record,
            'status' => $service->statusFor($profile->setRelation('identityVerification', $record)),
            'isRequired' => $service->isRequired(),
            'canAcceptJobs' => $service->professionalCanAcceptJobs($profile),
            'canStartVerification' => $didit->canStart($profile),
            'providerConfigured' => $didit->isConfigured(),
        ]);
    }

    public function start(
        StartIdentityVerificationRequest $request,
        DiditIdentityVerificationService $didit,
    ): RedirectResponse {
        $profile = $request->user()->professionalProfile()->firstOrFail();

        try {
            $result = $didit->start($profile, $request->ip(), $request->userAgent());

            return redirect()->away($result['url']);
        } catch (DiditException $exception) {
            Log::warning('Didit verification start failed', [
                'professional_id' => $profile->id,
                'reason' => $exception->reason,
            ]);

            return back()->withErrors(['identity_verification' => $this->messageFor($exception)]);
        }
    }

    public function callback(
        Request $request,
        DiditIdentityVerificationService $didit,
    ): RedirectResponse {
        $profile = $request->user()->professionalProfile()->firstOrFail();
        $sessionId = $request->string('verificationSessionId')->toString();
        $record = $profile->identityVerification()->first();

        if (! $record || ! filled($record->provider_session_id)
            || ! hash_equals((string) $record->provider_session_id, $sessionId)) {
            abort(404);
        }

        try {
            $didit->sync($profile);

            return redirect()->route('professional.identity-verification.show')
                ->with('status', 'Actualizamos el estado directamente con Didit.');
        } catch (DiditException $exception) {
            Log::notice('Didit callback awaiting provider decision', [
                'professional_id' => $profile->id,
                'reason' => $exception->reason,
            ]);

            return redirect()->route('professional.identity-verification.show')
                ->with('status', 'Estamos verificando tu identidad. El estado se actualizará en cuanto Didit termine.');
        }
    }

    private function messageFor(DiditException $exception): string
    {
        return match ($exception->reason) {
            'identity_already_verified' => 'Tu identidad ya está verificada.',
            'identity_session_already_active' => 'Ya tienes una verificación activa. Continúa desde el estado actual.',
            'didit_rate_limited' => 'Didit está procesando muchas solicitudes. Inténtalo nuevamente en unos minutos.',
            'didit_not_configured' => 'La verificación todavía no está disponible.',
            default => 'No pudimos iniciar la verificación. Inténtalo nuevamente más tarde.',
        };
    }
}
