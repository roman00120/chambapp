<?php

namespace App\Http\Controllers\Professional;

use App\Http\Controllers\Controller;
use App\Services\ProfessionalIdentityVerificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalIdentityVerificationController extends Controller
{
    public function __invoke(Request $request, ProfessionalIdentityVerificationService $service): View
    {
        $profile = $request->user()->professionalProfile()->firstOrFail();
        $record = $service->recordFor($profile);

        return view('professional.identity-verification.show', [
            'record' => $record,
            'status' => $service->statusFor($profile->setRelation('identityVerification', $record)),
            'isRequired' => $service->isRequired(),
            'canAcceptJobs' => $service->professionalCanAcceptJobs($profile),
        ]);
    }
}
