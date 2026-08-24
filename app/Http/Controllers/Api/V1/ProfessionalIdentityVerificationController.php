<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProfessionalIdentityVerificationResource;
use Illuminate\Http\Request;

class ProfessionalIdentityVerificationController extends Controller
{
    public function show(Request $request): ProfessionalIdentityVerificationResource
    {
        return new ProfessionalIdentityVerificationResource(
            $request->user()->professionalProfile()->firstOrFail(),
        );
    }
}
