<?php

namespace App\Http\Controllers;

use App\Exceptions\MercadoPagoException;
use App\Http\Requests\StoreTipRequest;
use App\Models\JobRequest;
use App\Services\PaymentService;
use DomainException;
use Illuminate\Http\RedirectResponse;

class TipController extends Controller
{
    public function store(StoreTipRequest $request, JobRequest $jobRequest, PaymentService $payments): RedirectResponse
    {
        $this->authorize('view', $jobRequest);

        try {
            $payment = $payments->startTipCheckout($jobRequest, $request->user(), (string) $request->validated('amount'));
        } catch (MercadoPagoException|DomainException $exception) {
            return back()->withErrors(['tip' => $exception->getMessage()]);
        }

        return redirect()->away((string) $payment->checkout_url);
    }
}
