<?php

namespace App\Services;

use App\Enums\DisciplinaryActionStatus;
use App\Enums\DisciplinaryActionType;
use App\Enums\DisciplinaryAppealStatus;
use App\Enums\ReportCategory;
use App\Enums\ReportSeverity;
use App\Enums\UserStatus;
use App\Models\DisciplinaryAction;
use App\Models\DisciplinaryAppeal;
use App\Models\JobRequest;
use App\Models\ReportEvidence;
use App\Models\User;
use App\Models\UserReport;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DisciplinaryService
{
    public function __construct(
        private readonly AdminAuditService $audit,
    ) {}

    public function createReport(User $reporter, array $data, array $files = []): UserReport
    {
        $reportedId = (int) ($data['reported_id'] ?? 0);
        if ($reportedId <= 0 || $reporter->id === $reportedId) {
            throw new DomainException('No puedes reportarte a ti mismo.');
        }

        $reported = User::query()->findOrFail($reportedId);

        // Rate limit: max 5 reports per hour
        $recentReportsCount = UserReport::query()
            ->where('reporter_id', $reporter->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentReportsCount >= 5) {
            throw new DomainException('Has alcanzado el límite de reportes por hora. Por favor intenta más tarde.');
        }

        $jobRequestId = isset($data['job_request_id']) ? (int) $data['job_request_id'] : null;
        if ($jobRequestId) {
            $job = JobRequest::query()->with('professional')->findOrFail($jobRequestId);
            $isClient = $job->client_id === $reporter->id && $job->professional?->user_id === $reported->id;
            $isPro = $job->professional?->user_id === $reporter->id && $job->client_id === $reported->id;

            if (! $isClient && ! $isPro) {
                throw new DomainException('El trabajo especificado no corresponde a una relación entre ambas partes.');
            }
        }

        $category = ReportCategory::from((string) $data['category']);
        $severity = isset($data['severity_reported'])
            ? ReportSeverity::tryFrom((string) $data['severity_reported']) ?? ReportSeverity::LOW
            : ReportSeverity::LOW;

        return DB::transaction(function () use ($reporter, $reported, $jobRequestId, $category, $severity, $data, $files): UserReport {
            $report = UserReport::query()->create([
                'reporter_id' => $reporter->id,
                'reported_id' => $reported->id,
                'job_request_id' => $jobRequestId,
                'category' => $category,
                'severity_reported' => $severity,
                'description' => trim((string) $data['description']),
                'status' => 'submitted',
            ]);

            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $originalName = Str::limit($file->getClientOriginalName(), 200);
                    $mime = $file->getMimeType() ?: 'application/octet-stream';
                    $size = $file->getSize();
                    $path = $file->store('reports/evidence/'.$report->id, 'local');

                    ReportEvidence::query()->create([
                        'user_report_id' => $report->id,
                        'uploaded_by_user_id' => $reporter->id,
                        'storage_path' => $path,
                        'mime_type' => $mime,
                        'original_name' => $originalName,
                        'file_size' => $size,
                    ]);
                }
            }

            return $report->fresh(['reporter', 'reported', 'evidence']);
        });
    }

    public function resolveReport(
        UserReport $report,
        User $admin,
        string $decision,
        ?string $reasonCode = null,
        ?string $reasonText = null,
        ?string $actionType = null,
        ?string $notes = null,
        ?int $suspensionDays = null,
    ): UserReport {
        if (! $admin->isAdmin()) {
            throw new DomainException('Solo un administrador puede resolver reportes.');
        }

        if ($admin->id === $report->reporter_id || $admin->id === $report->reported_id) {
            throw new DomainException('No puedes resolver un reporte en el que estás involucrado.');
        }

        if (in_array($report->status, ['resolved_valid', 'resolved_invalid', 'closed'], true)) {
            throw new DomainException('Este reporte ya fue resuelto previamente.');
        }

        return DB::transaction(function () use ($report, $admin, $decision, $reasonCode, $reasonText, $actionType, $notes, $suspensionDays): UserReport {
            $reportedUser = $report->reported;

            switch ($decision) {
                case 'invalid':
                    $report->forceFill([
                        'status' => 'resolved_invalid',
                        'resolution' => 'no_action',
                        'admin_notes_private' => $notes,
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => now(),
                    ])->save();
                    break;

                case 'valid_yellow_card':
                    $report->forceFill([
                        'status' => 'resolved_valid',
                        'resolution' => 'yellow_card',
                        'admin_notes_private' => $notes,
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => now(),
                    ])->save();

                    DisciplinaryAction::query()->create([
                        'user_id' => $reportedUser->id,
                        'source_report_id' => $report->id,
                        'action_type' => DisciplinaryActionType::YELLOW_CARD,
                        'severity' => $report->severity_reported,
                        'reason_code' => $reasonCode ?: $report->category->value,
                        'reason_text' => $reasonText ?: 'Advertencia emitida por moderación tras revisión de reporte.',
                        'status' => DisciplinaryActionStatus::ACTIVE,
                        'issued_by_admin_id' => $admin->id,
                        'issued_at' => now(),
                        'expires_at' => now()->addMonths(6),
                        'internal_notes' => $notes,
                    ]);
                    break;

                case 'valid_severe':
                    $parsedActionType = DisciplinaryActionType::tryFrom((string) $actionType) ?? DisciplinaryActionType::TEMPORARY_SUSPENSION;
                    $report->forceFill([
                        'status' => 'resolved_valid',
                        'resolution' => $parsedActionType->value,
                        'admin_notes_private' => $notes,
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => now(),
                    ])->save();

                    $expiresAt = null;
                    if ($parsedActionType === DisciplinaryActionType::TEMPORARY_SUSPENSION && $suspensionDays) {
                        $expiresAt = now()->addDays($suspensionDays);
                    }

                    DisciplinaryAction::query()->create([
                        'user_id' => $reportedUser->id,
                        'source_report_id' => $report->id,
                        'action_type' => $parsedActionType,
                        'severity' => ReportSeverity::CRITICAL,
                        'reason_code' => $reasonCode ?: $report->category->value,
                        'reason_text' => $reasonText ?: 'Sanción disciplinaria directa aplicada por administración.',
                        'status' => DisciplinaryActionStatus::ACTIVE,
                        'issued_by_admin_id' => $admin->id,
                        'issued_at' => now(),
                        'expires_at' => $expiresAt,
                        'internal_notes' => $notes,
                    ]);

                    if ($parsedActionType === DisciplinaryActionType::BAN) {
                        $reportedUser->update(['status' => UserStatus::BLOCKED]);
                    } elseif (in_array($parsedActionType, [DisciplinaryActionType::TEMPORARY_SUSPENSION, DisciplinaryActionType::INDEFINITE_SUSPENSION], true)) {
                        $reportedUser->update(['status' => UserStatus::SUSPENDED]);
                    }
                    break;

                case 'close_no_action':
                default:
                    $report->forceFill([
                        'status' => 'closed',
                        'resolution' => 'dismissed',
                        'admin_notes_private' => $notes,
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => now(),
                    ])->save();
                    break;
            }

            return $report->fresh(['reporter', 'reported', 'reviewer', 'disciplinaryAction']);
        });
    }

    public function submitAppeal(DisciplinaryAction $action, User $user, string $appealText): DisciplinaryAppeal
    {
        if ($action->user_id !== $user->id) {
            throw new DomainException('No puedes apelar una sanción que no te pertenece.');
        }

        if (! $action->isActive()) {
            throw new DomainException('Esta sanción ya no se encuentra activa para ser apelada.');
        }

        $pendingAppeal = DisciplinaryAppeal::query()
            ->where('disciplinary_action_id', $action->id)
            ->whereIn('status', [DisciplinaryAppealStatus::SUBMITTED->value, DisciplinaryAppealStatus::UNDER_REVIEW->value])
            ->exists();

        if ($pendingAppeal) {
            throw new DomainException('Ya existe una apelación en curso para esta sanción.');
        }

        return DisciplinaryAppeal::query()->create([
            'disciplinary_action_id' => $action->id,
            'user_id' => $user->id,
            'appeal_text' => trim($appealText),
            'status' => DisciplinaryAppealStatus::SUBMITTED,
        ]);
    }

    public function resolveAppeal(
        DisciplinaryAppeal $appeal,
        User $admin,
        bool $accepted,
        ?string $notes = null,
    ): DisciplinaryAppeal {
        if (! $admin->isAdmin()) {
            throw new DomainException('Solo un administrador puede resolver apelaciones.');
        }

        if ($admin->id === $appeal->user_id) {
            throw new DomainException('No puedes resolver tu propia apelación.');
        }

        if (in_array($appeal->status, [DisciplinaryAppealStatus::ACCEPTED, DisciplinaryAppealStatus::REJECTED], true)) {
            throw new DomainException('Esta apelación ya fue resuelta.');
        }

        return DB::transaction(function () use ($appeal, $admin, $accepted, $notes): DisciplinaryAppeal {
            $action = $appeal->disciplinaryAction;
            $user = $appeal->user;

            if ($accepted) {
                $appeal->forceFill([
                    'status' => DisciplinaryAppealStatus::ACCEPTED,
                    'reviewed_by_admin_id' => $admin->id,
                    'reviewed_at' => now(),
                    'resolution_notes' => $notes,
                ])->save();

                $action->forceFill([
                    'status' => DisciplinaryActionStatus::REVOKED,
                    'revoked_at' => now(),
                    'revoked_by_admin_id' => $admin->id,
                    'revocation_reason' => $notes ?: 'Apelación aceptada por administración.',
                ])->save();

                // If user was suspended due to this action, check if they have other active suspensions
                $hasOtherActiveSuspension = DisciplinaryAction::query()
                    ->where('user_id', $user->id)
                    ->where('id', '!=', $action->id)
                    ->where('status', DisciplinaryActionStatus::ACTIVE->value)
                    ->whereIn('action_type', [
                        DisciplinaryActionType::TEMPORARY_SUSPENSION->value,
                        DisciplinaryActionType::INDEFINITE_SUSPENSION->value,
                        DisciplinaryActionType::BAN->value,
                    ])
                    ->exists();

                if (! $hasOtherActiveSuspension && $user->isSuspended()) {
                    $user->update(['status' => UserStatus::ACTIVE]);
                }
            } else {
                $appeal->forceFill([
                    'status' => DisciplinaryAppealStatus::REJECTED,
                    'reviewed_by_admin_id' => $admin->id,
                    'reviewed_at' => now(),
                    'resolution_notes' => $notes,
                ])->save();
            }

            return $appeal->fresh(['disciplinaryAction', 'user', 'reviewer']);
        });
    }
}
