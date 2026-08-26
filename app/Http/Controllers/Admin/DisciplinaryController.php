<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DisciplinaryActionType;
use App\Enums\ReportCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResolveDisciplinaryAppealRequest;
use App\Http\Requests\ResolveUserReportRequest;
use App\Models\DisciplinaryAction;
use App\Models\DisciplinaryAppeal;
use App\Models\ReportEvidence;
use App\Models\UserReport;
use App\Services\AdminAuditService;
use App\Services\DisciplinaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DisciplinaryController extends Controller
{
    public function __construct(
        private readonly DisciplinaryService $disciplinary,
        private readonly AdminAuditService $audit,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $category = $request->query('category');

        $reports = UserReport::query()
            ->with(['reporter', 'reported', 'jobRequest', 'evidence', 'disciplinaryAction'])
            ->when($status, fn ($query, $s) => $query->where('status', $s))
            ->when($category, fn ($query, $c) => $query->where('category', $c))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $pendingAppealsCount = DisciplinaryAppeal::query()
            ->where('status', 'submitted')
            ->count();

        return view('admin.reports.index', [
            'reports' => $reports,
            'categories' => ReportCategory::cases(),
            'pendingAppealsCount' => $pendingAppealsCount,
        ]);
    }

    public function show(UserReport $report): View
    {
        $report->load(['reporter', 'reported.disciplinaryActions', 'jobRequest', 'evidence.uploader', 'reviewer', 'disciplinaryAction']);

        $reportedUser = $report->reported;
        $activeYellowCards = $reportedUser ? $reportedUser->activeYellowCardsCount() : 0;
        $totalPreviousReports = $reportedUser ? UserReport::where('reported_id', $reportedUser->id)->where('id', '!=', $report->id)->count() : 0;

        return view('admin.reports.show', [
            'report' => $report,
            'activeYellowCards' => $activeYellowCards,
            'totalPreviousReports' => $totalPreviousReports,
            'actionTypes' => DisciplinaryActionType::cases(),
        ]);
    }

    public function resolve(ResolveUserReportRequest $request, UserReport $report): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->disciplinary->resolveReport(
                $report,
                $request->user(),
                $validated['decision'],
                $validated['reason_code'] ?? null,
                $validated['reason_text'] ?? null,
                $validated['action_type'] ?? null,
                $validated['admin_notes_private'] ?? null,
                isset($validated['suspension_days']) ? (int) $validated['suspension_days'] : null,
            );

            $this->audit->record($request->user(), 'disciplinary.report.resolved', $report, [
                'decision' => $validated['decision'],
                'reported_id' => $report->reported_id,
            ], $request);

            return redirect()->route('admin.reports.show', $report)->with('status', 'Reporte resuelto correctamente.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function appeals(Request $request): View
    {
        $appeals = DisciplinaryAppeal::query()
            ->with(['user', 'disciplinaryAction.sourceReport', 'reviewer'])
            ->when($request->query('status'), fn ($query, $s) => $query->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.appeals', [
            'appeals' => $appeals,
        ]);
    }

    public function resolveAppeal(ResolveDisciplinaryAppealRequest $request, DisciplinaryAppeal $appeal): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->disciplinary->resolveAppeal(
                $appeal,
                $request->user(),
                (bool) $validated['accepted'],
                $validated['resolution_notes'] ?? null,
            );

            $this->audit->record($request->user(), 'disciplinary.appeal.resolved', $appeal, [
                'accepted' => (bool) $validated['accepted'],
                'user_id' => $appeal->user_id,
            ], $request);

            return redirect()->route('admin.reports.appeals')->with('status', 'Apelación resuelta correctamente.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function downloadEvidence(ReportEvidence $evidence): StreamedResponse
    {
        if (! auth()->user()?->isAdmin() && auth()->id() !== $evidence->uploaded_by_user_id) {
            abort(403, 'No tienes permiso para descargar esta evidencia.');
        }

        if (! Storage::disk('local')->exists($evidence->storage_path)) {
            abort(404, 'El archivo de evidencia no fue encontrado.');
        }

        return Storage::disk('local')->download($evidence->storage_path, $evidence->original_name);
    }
}
