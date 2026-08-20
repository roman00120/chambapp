<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectJobQuoteRequest;
use App\Models\JobQuote;
use App\Services\JobWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;

class ClientQuoteController extends Controller
{
    public function accept(JobQuote $jobQuote, JobWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('accept', $jobQuote);

        try {
            $workflow->acceptQuote($jobQuote, request()->user());
        } catch (DomainException $exception) {
            return back()->withErrors(['quote' => $exception->getMessage()]);
        }

        return back()->with('status', 'Cotización aceptada. El siguiente paso es pagar en Chambapp.');
    }

    public function reject(RejectJobQuoteRequest $request, JobQuote $jobQuote, JobWorkflowService $workflow): RedirectResponse
    {
        try {
            $reason = $request->validated('reason');
            $detail = $request->validated('reason_detail');
            $workflow->rejectQuote($jobQuote, $request->user(), filled($detail) ? $reason.': '.$detail : $reason);
        } catch (DomainException $exception) {
            return back()->withErrors(['quote' => $exception->getMessage()]);
        }

        return back()->with('status', 'Cotización rechazada. El profesional podrá enviar otra propuesta.');
    }
}
