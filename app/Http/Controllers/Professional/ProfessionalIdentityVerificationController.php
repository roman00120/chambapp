<?php

namespace App\Http\Controllers\Professional;

use App\Exceptions\DiditException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StartIdentityVerificationRequest;
use App\Services\DiditIdentityVerificationService;
use App\Services\IdentityVerificationTransferService;
use App\Services\ProfessionalIdentityVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ProfessionalIdentityVerificationController extends Controller
{
    public function __invoke(
        Request $request,
        ProfessionalIdentityVerificationService $service,
        DiditIdentityVerificationService $didit,
    ): Response {
        $profile = $request->user()->professionalProfile()->firstOrFail();
        $record = $service->recordFor($profile);

        return response()->view('professional.identity-verification.show', [
            'record' => $record,
            'status' => $service->statusFor($profile->setRelation('identityVerification', $record)),
            'isRequired' => $service->isRequired(),
            'canAcceptJobs' => $service->professionalCanAcceptJobs($profile),
            'canStartVerification' => $didit->canStart($profile),
            'providerConfigured' => $didit->isConfigured(),
            'mobileTransferUrl' => $request->session()->get('identity_mobile_transfer_url'),
            'mobileTransferExpiresAt' => $request->session()->get('identity_mobile_transfer_expires_at'),
        ])->withHeaders([
            'Cache-Control' => 'no-store, private',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }

    public function start(
        StartIdentityVerificationRequest $request,
        DiditIdentityVerificationService $didit,
        IdentityVerificationTransferService $transfers,
    ): RedirectResponse {
        $profile = $request->user()->professionalProfile()->firstOrFail();

        try {
            $result = $didit->start($profile, $request->ip(), $request->userAgent());
            $transfer = $transfers->issue($profile, $result['verification'], $result['url']);

            return redirect()->route('professional.identity-verification.show')->with([
                'identity_mobile_transfer_url' => $transfer['url'],
                'identity_mobile_transfer_expires_at' => $transfer['expires_at']->toIso8601String(),
                'status' => 'La sesiÃ³n segura estÃ¡ lista. ContinÃºa en tu celular o Ã¡brela aquÃ­.',
            ]);
        } catch (DiditException $exception) {
            Log::warning('Didit verification start failed', [
                'professional_id' => $profile->id,
                'reason' => $exception->reason,
            ]);

            return back()->withErrors(['identity_verification' => $this->messageFor($exception)]);
        }
    }

    public function status(
        Request $request,
        ProfessionalIdentityVerificationService $service,
    ): JsonResponse {
        $profile = $request->user()->professionalProfile()->firstOrFail();
        $record = $service->recordFor($profile);
        $status = $service->statusFor($profile->setRelation('identityVerification', $record));

        return response()->json([
            'status' => $status->value,
            'identity_verified' => $service->hasVerifiedIdentity($profile),
            'message' => match ($status->value) {
                'verified' => 'VerificaciÃ³n completada.',
                'needs_review' => 'Tu verificaciÃ³n estÃ¡ en revisiÃ³n.',
                'rejected' => 'No pudimos verificar tu identidad.',
                'expired' => 'La sesiÃ³n de verificaciÃ³n expirÃ³.',
                default => 'Esperando verificaciÃ³n...',
            },
        ])->withHeaders(['Cache-Control' => 'no-store, private']);
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
