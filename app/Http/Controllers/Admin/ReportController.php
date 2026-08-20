<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminReportStatusRequest;
use App\Models\JobRequest;
use App\Models\ProfessionalProfile;
use App\Models\Report;
use App\Models\Review;
use App\Models\Service;
use App\Services\AdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    private const ALLOWED_TYPES = [Review::class, Service::class, JobRequest::class, ProfessionalProfile::class];

    public function index(Request $request): View
    {
        $reports = Report::query()->with(['reporter', 'reviewer'])
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.index', compact('reports'));
    }

    public function show(Report $report): View
    {
        $report->load(['reporter', 'reviewer']);
        if (in_array($report->reportable_type, self::ALLOWED_TYPES, true)) {
            $report->load('reportable');
        }

        return view('admin.reports.show', compact('report'));
    }

    public function status(AdminReportStatusRequest $request, Report $report, AdminAuditService $audit): RedirectResponse
    {
        $status = $request->validated('status');
        $report->forceFill([
            'status' => $status,
            'reviewed_by' => $request->user()->getKey(),
            'reviewed_at' => now(),
        ])->save();
        $audit->record($request->user(), 'report.'.$status, $report, ['status' => $status], $request);

        return back()->with('status', 'Reporte actualizado correctamente.');
    }
}
