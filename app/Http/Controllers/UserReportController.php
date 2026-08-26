<?php

namespace App\Http\Controllers;

use App\Enums\ReportCategory;
use App\Http\Requests\StoreDisciplinaryAppealRequest;
use App\Http\Requests\StoreUserReportRequest;
use App\Models\DisciplinaryAction;
use App\Models\JobRequest;
use App\Models\User;
use App\Models\UserReport;
use App\Services\DisciplinaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserReportController extends Controller
{
    public function __construct(
        private readonly DisciplinaryService $disciplinary,
    ) {}

    public function securityCenter(Request $request): View
    {
        $user = $request->user();

        $submittedReports = UserReport::query()
            ->with(['reported', 'jobRequest'])
            ->where('reporter_id', $user->id)
            ->latest()
            ->paginate(10, ['*'], 'reports_page');

        $disciplinaryActions = DisciplinaryAction::query()
            ->with(['appeals', 'latestAppeal'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10, ['*'], 'actions_page');

        return view('account.security', [
            'user' => $user,
            'submittedReports' => $submittedReports,
            'disciplinaryActions' => $disciplinaryActions,
            'activeYellowCards' => $user->activeYellowCardsCount(),
        ]);
    }

    public function create(Request $request): View
    {
        $reportedUserId = $request->query('user_id');
        $jobRequestId = $request->query('job_id');

        $reportedUser = $reportedUserId ? User::query()->find($reportedUserId) : null;
        $jobRequest = $jobRequestId ? JobRequest::query()->find($jobRequestId) : null;

        return view('reports.create', [
            'reportedUser' => $reportedUser,
            'jobRequest' => $jobRequest,
            'categories' => ReportCategory::cases(),
        ]);
    }

    public function store(StoreUserReportRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $files = $request->file('evidence', []);

        try {
            $this->disciplinary->createReport($request->user(), $data, is_array($files) ? $files : []);

            return redirect()->route('account.security')->with('status', 'Tu reporte fue enviado exitosamente. Nuestro equipo lo revisará cuidadosamente.');
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function appeal(StoreDisciplinaryAppealRequest $request, DisciplinaryAction $action): RedirectResponse
    {
        if ($action->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para apelar esta sanción.');
        }

        try {
            $this->disciplinary->submitAppeal($action, $request->user(), (string) $request->validated('appeal_text'));

            return redirect()->route('account.security')->with('status', 'Tu apelación fue enviada y será revisada por el equipo administrativo.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
