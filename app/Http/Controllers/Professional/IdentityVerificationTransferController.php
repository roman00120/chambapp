<?php

namespace App\Http\Controllers\Professional;

use App\Exceptions\IdentityVerificationTransferException;
use App\Http\Controllers\Controller;
use App\Services\IdentityVerificationTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IdentityVerificationTransferController extends Controller
{
    public function __invoke(
        Request $request,
        string $token,
        IdentityVerificationTransferService $transfers,
    ): RedirectResponse {
        $profile = $request->user()->professionalProfile()->firstOrFail();

        try {
            $hostedUrl = $transfers->redeem($profile, $token);
        } catch (IdentityVerificationTransferException $exception) {
            abort($exception->httpStatus);
        }

        return redirect()->away($hostedUrl)->withHeaders([
            'Cache-Control' => 'no-store, private',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
