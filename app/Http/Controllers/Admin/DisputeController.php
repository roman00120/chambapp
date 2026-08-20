<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminDisputeStatusRequest;
use App\Models\JobDispute;
use App\Services\AdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function index(Request $request): View
    {
        $disputes = JobDispute::query()
            ->with(['jobRequest.client', 'jobRequest.professional.user', 'opener'])
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.disputes.index', compact('disputes'));
    }

    public function show(JobDispute $dispute): View
    {
        return view('admin.disputes.show', ['dispute' => $dispute->load(['jobRequest.client', 'jobRequest.professional.user', 'jobRequest.service', 'jobRequest.quotes', 'jobRequest.payment', 'opener', 'resolver'])]);
    }

    public function status(AdminDisputeStatusRequest $request, JobDispute $dispute, AdminAuditService $audit): RedirectResponse
    {
        $status = $request->validated('status');
        $dispute->forceFill([
            'status' => $status,
            'resolved_by' => in_array($status, ['resolved', 'rejected'], true) ? $request->user()->getKey() : null,
            'resolved_at' => in_array($status, ['resolved', 'rejected'], true) ? now() : null,
        ])->save();
        $audit->record($request->user(), 'dispute.'.$status, $dispute, ['status' => $status], $request);

        return back()->with('status', 'Disputa actualizada sin modificar el pago asociado.');
    }
}
