<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Exceptions\MercadoPagoException;
use App\Models\JobRequest;
use App\Services\PaymentCalculationService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function summary(Request $request, JobRequest $jobRequest, PaymentCalculationService $calculation): View
    {
        $this->authorize('paymentSummary', $jobRequest);
        $jobRequest->load(['service', 'professional.user', 'quotes', 'payment']);
        $money = $calculation->forJob($jobRequest);

        return view('payments.summary', [
            'jobRequest' => $jobRequest,
            'calculation' => $money,
            'canCheckout' => Gate::forUser($request->user())->allows('pay', $jobRequest),
            'hasApprovedPayment' => $jobRequest->payment?->status === PaymentStatus::APPROVED,
        ]);
    }

    public function checkout(Request $request, JobRequest $jobRequest, PaymentService $payments): RedirectResponse
    {
        $this->authorize('pay', $jobRequest);

        try {
            $payment = $payments->startCheckout($jobRequest, $request->user());
        } catch (MercadoPagoException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        } catch (\DomainException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return redirect()->away((string) $payment->checkout_url);
    }

    public function success(Request $request): View
    {
        return $this->returnView('success');
    }

    public function pending(Request $request): View
    {
        return $this->returnView('pending');
    }

    public function error(Request $request): View
    {
        return $this->returnView('error');
    }

    private function returnView(string $state): View
    {
        return view('payments.return', ['state' => $state]);
    }
}
