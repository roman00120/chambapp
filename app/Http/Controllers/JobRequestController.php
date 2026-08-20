<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\JobRequest;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class JobRequestController extends Controller
{
    public function show(Request $request, JobRequest $jobRequest): View
    {
        $jobRequest->load(['service.category', 'client', 'professional.user', 'quotes.professional.user', 'payment', 'review.client', 'review.jobRequest.service', 'dispute']);
        $this->authorize('view', $jobRequest);
        $jobRequest->quotes->each->markExpiredIfNeeded();
        $jobRequest->load('quotes.professional.user');

        return view('jobs.show', [
            'jobRequest' => $jobRequest,
            'isClient' => $request->user()->getKey() === $jobRequest->client_id,
            'hasApprovedPayment' => $jobRequest->payment?->status === PaymentStatus::APPROVED,
            'canPay' => Gate::forUser($request->user())->allows('pay', $jobRequest),
            'canReview' => Gate::forUser($request->user())->allows('create', [Review::class, $jobRequest]),
            'canDispute' => Gate::forUser($request->user())->allows('dispute', $jobRequest),
        ]);
    }
}
