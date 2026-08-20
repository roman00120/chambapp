<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\VerificationStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalEarningsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $profile = $request->user()->professionalProfile()->firstOrCreate([
            'user_id' => $request->user()->getKey(),
        ], [
            'verification_status' => VerificationStatus::UNVERIFIED,
        ]);
        $payments = $profile
            ->payments()
            ->where('status', PaymentStatus::APPROVED->value)
            ->with(['jobRequest.service'])
            ->latest()
            ->paginate(12);

        return view('payments.earnings', compact('payments'));
    }
}
