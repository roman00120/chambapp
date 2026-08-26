<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDisciplinaryAppealRequest;
use App\Http\Requests\StoreUserReportRequest;
use App\Models\DisciplinaryAction;
use App\Models\UserReport;
use App\Services\DisciplinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserReportController extends Controller
{
    public function __construct(
        private readonly DisciplinaryService $disciplinary,
    ) {}

    public function store(StoreUserReportRequest $request): JsonResponse
    {
        $data = $request->validated();
        $files = $request->file('evidence', []);

        try {
            $report = $this->disciplinary->createReport($request->user(), $data, is_array($files) ? $files : []);

            return response()->json([
                'data' => [
                    'id' => $report->id,
                    'category' => $report->category->value,
                    'category_label' => $report->category->label(),
                    'severity' => $report->severity_reported->value,
                    'status' => $report->status,
                    'created_at' => $report->created_at->toISOString(),
                ],
                'message' => 'Reporte enviado. Nuestro equipo lo revisará. Un reporte no genera una sanción automática.',
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function myReports(Request $request): JsonResponse
    {
        $reports = UserReport::query()
            ->with('reported:id,name')
            ->where('reporter_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => $reports->map(fn (UserReport $r) => [
                'id' => $r->id,
                'reported_user' => [
                    'id' => $r->reported?->id,
                    'name' => $r->reported?->name,
                ],
                'category' => $r->category->value,
                'category_label' => $r->category->label(),
                'status' => $r->status,
                'created_at' => $r->created_at->toISOString(),
            ]),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    public function myDisciplinaryActions(Request $request): JsonResponse
    {
        $actions = DisciplinaryAction::query()
            ->with('latestAppeal')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => [
                'active_yellow_cards' => $request->user()->activeYellowCardsCount(),
                'actions' => $actions->map(fn (DisciplinaryAction $a) => [
                    'id' => $a->id,
                    'action_type' => $a->action_type->value,
                    'action_type_label' => $a->action_type->label(),
                    'severity' => $a->severity->value,
                    'reason_text' => $a->reason_text,
                    'status' => $a->status->value,
                    'status_label' => $a->status->label(),
                    'issued_at' => $a->issued_at->toISOString(),
                    'expires_at' => $a->expires_at?->toISOString(),
                    'is_active' => $a->isActive(),
                    'appeal' => $a->latestAppeal ? [
                        'id' => $a->latestAppeal->id,
                        'status' => $a->latestAppeal->status->value,
                        'status_label' => $a->latestAppeal->status->label(),
                        'created_at' => $a->latestAppeal->created_at->toISOString(),
                    ] : null,
                ]),
            ],
        ]);
    }

    public function appeal(StoreDisciplinaryAppealRequest $request, DisciplinaryAction $action): JsonResponse
    {
        if ($action->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No tienes permiso para apelar esta sanción.'], 403);
        }

        try {
            $appeal = $this->disciplinary->submitAppeal($action, $request->user(), (string) $request->validated('appeal_text'));

            return response()->json([
                'data' => [
                    'id' => $appeal->id,
                    'status' => $appeal->status->value,
                    'status_label' => $appeal->status->label(),
                    'created_at' => $appeal->created_at->toISOString(),
                ],
                'message' => 'Tu apelación fue enviada y será revisada por el equipo administrativo.',
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
