<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelJobRequest;
use App\Models\JobRequest;
use App\Services\JobWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobWorkflowController extends Controller
{
    public function reject(Request $request, JobRequest $jobRequest, JobWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('reject', $jobRequest);

        try {
            $workflow->reject($jobRequest);
        } catch (DomainException $exception) {
            return back()->withErrors(['job' => $exception->getMessage()]);
        }

        return back()->with('status', 'Solicitud rechazada.');
    }

    public function acceptDirect(Request $request, JobRequest $jobRequest, JobWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('acceptDirect', $jobRequest);

        try {
            $workflow->acceptDirectJob($jobRequest, $request->user());
        } catch (DomainException $exception) {
            return back()->withErrors(['job' => $exception->getMessage()]);
        }

        return back()->with('status', 'Solicitud aceptada correctamente. Esperando pago del cliente.');
    }

    public function start(Request $request, JobRequest $jobRequest, JobWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('start', $jobRequest);

        try {
            $workflow->start($jobRequest);
        } catch (DomainException $exception) {
            return back()->withErrors(['job' => $exception->getMessage()]);
        }

        return back()->with('status', 'Trabajo iniciado.');
    }

    public function finish(Request $request, JobRequest $jobRequest, JobWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('finish', $jobRequest);

        try {
            $workflow->finish($jobRequest);
        } catch (DomainException $exception) {
            return back()->withErrors(['job' => $exception->getMessage()]);
        }

        return back()->with('status', 'Trabajo marcado como terminado. Espera la confirmación del cliente.');
    }

    public function onTheWay(Request $request, JobRequest $jobRequest, JobWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('onTheWay', $jobRequest);
        try {
            $workflow->onTheWay($jobRequest);
        } catch (DomainException $exception) {
            return back()->withErrors(['job' => $exception->getMessage()]);
        }

        return back()->with('status', 'Avisamos que vas en camino.');
    }

    public function arrive(Request $request, JobRequest $jobRequest, JobWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('arrive', $jobRequest);
        try {
            $workflow->arrive($jobRequest);
        } catch (DomainException $exception) {
            return back()->withErrors(['job' => $exception->getMessage()]);
        }

        return back()->with('status', 'Marcaste tu llegada.');
    }

    public function complete(Request $request, JobRequest $jobRequest, JobWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('complete', $jobRequest);
        $request->validate(['completion_code' => ['required', 'digits:6']]);

        try {
            $workflow->confirmCompletion($jobRequest, (string) $request->input('completion_code'));
        } catch (DomainException $exception) {
            return back()->withErrors(['job' => $exception->getMessage()]);
        }

        return back()->with('status', 'Trabajo confirmado como completado.');
    }

    public function cancel(CancelJobRequest $request, JobRequest $jobRequest, JobWorkflowService $workflow): RedirectResponse
    {
        try {
            $workflow->cancel($jobRequest, $request->validated('cancellation_reason'), $request->user());
        } catch (DomainException $exception) {
            return back()->withErrors(['job' => $exception->getMessage()]);
        }

        return back()->with('status', 'Trabajo cancelado.');
    }
}
