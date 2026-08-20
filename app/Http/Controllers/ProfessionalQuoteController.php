<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobQuoteRequest;
use App\Models\JobRequest;
use App\Services\JobWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;

class ProfessionalQuoteController extends Controller
{
    public function store(StoreJobQuoteRequest $request, JobRequest $jobRequest, JobWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('create', $jobRequest);

        try {
            $workflow->createQuote(
                $jobRequest,
                $request->user(),
                $request->validated('amount'),
                $request->validated('description'),
            );
        } catch (DomainException $exception) {
            return back()->withErrors(['quote' => $exception->getMessage()]);
        }

        return back()->with('status', 'Cotización enviada al cliente.');
    }
}
