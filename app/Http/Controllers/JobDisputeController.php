<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobDisputeRequest;
use App\Models\JobDispute;
use App\Models\JobRequest;
use App\Services\JobWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;

class JobDisputeController extends Controller
{
    public function store(StoreJobDisputeRequest $request, JobRequest $jobRequest, JobWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('create', [JobDispute::class, $jobRequest]);

        try {
            $workflow->openDispute(
                $jobRequest,
                $request->user(),
                $request->validated('reason'),
                $request->validated('description'),
            );
        } catch (DomainException $exception) {
            return back()->withErrors(['dispute' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('job-requests.show', $jobRequest)->with('status', 'El problema fue enviado a revisión.');
    }
}
