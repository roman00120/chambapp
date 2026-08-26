<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\DiditException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StartIdentityVerificationRequest;
use App\Http\Resources\Api\V1\ProfessionalIdentityVerificationResource;
use App\Services\DiditIdentityVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfessionalIdentityVerificationController extends Controller
{
    public function show(Request $request): ProfessionalIdentityVerificationResource
    {
        return new ProfessionalIdentityVerificationResource(
            $request->user()->professionalProfile()->firstOrFail(),
        );
    }

    public function start(
        StartIdentityVerificationRequest $request,
        DiditIdentityVerificationService $didit,
    ): JsonResponse {
        $profile = $request->user()->professionalProfile()->firstOrFail();

        try {
            $result = $didit->start(
                $profile,
                $request->ip(),
                $request->userAgent(),
                route('identity-verification.mobile-return'),
            );
        } catch (DiditException $exception) {
            return response()->json([
                'message' => $this->messageFor($exception),
                'errors' => (object) [],
                'code' => strtoupper($exception->reason),
            ], $exception->httpStatus);
        }

        return response()->json([
            'data' => [
                'verification_url' => $result['url'],
                'status' => $result['verification']->status->value,
            ],
        ], 201);
    }

    public function sync(Request $request, DiditIdentityVerificationService $didit): ProfessionalIdentityVerificationResource|JsonResponse
    {
        $profile = $request->user()->professionalProfile()->firstOrFail();

        try {
            $didit->sync($profile);
        } catch (DiditException $exception) {
            return response()->json([
                'message' => 'No pudimos actualizar el estado en este momento.',
                'errors' => (object) [],
                'code' => strtoupper($exception->reason),
            ], $exception->httpStatus);
        }

        $profile->unsetRelation('identityVerification');

        return new ProfessionalIdentityVerificationResource($profile);
    }

    private function messageFor(DiditException $exception): string
    {
        return match ($exception->reason) {
            'identity_already_verified' => 'Tu identidad ya está verificada.',
            'identity_session_already_active' => 'Ya tienes una verificación activa.',
            'didit_rate_limited' => 'Inténtalo nuevamente en unos minutos.',
            'didit_not_configured' => 'La verificación todavía no está disponible.',
            default => 'No pudimos iniciar la verificación en este momento.',
        };
    }
}
