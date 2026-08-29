<?php

namespace Tests\Feature;

use App\Enums\DisciplinaryActionStatus;
use App\Enums\DisciplinaryActionType;
use App\Enums\DisciplinaryAppealStatus;
use App\Enums\ReportCategory;
use App\Enums\ReportSeverity;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\DisciplinaryAction;
use App\Models\DisciplinaryAppeal;
use App\Models\JobRequest;
use App\Models\ProfessionalProfile;
use App\Models\ReportEvidence;
use App\Models\User;
use App\Models\UserReport;
use App\Services\DisciplinaryService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DisciplinarySystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_report_professional_with_evidence(): void
    {
        Storage::fake('local');

        $client = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL, 'status' => UserStatus::ACTIVE]);
        $profile = ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);

        $service = app(DisciplinaryService::class);
        $file = UploadedFile::fake()->create('prueba.png', 100, 'image/png');

        $report = $service->createReport($client, [
            'reported_id' => $proUser->id,
            'category' => ReportCategory::HARASSMENT->value,
            'severity_reported' => ReportSeverity::HIGH->value,
            'description' => 'El profesional tuvo una conducta irrespetuosa e inapropiada.',
        ], [$file]);

        $this->assertEquals('submitted', $report->status);
        $this->assertEquals($client->id, $report->reporter_id);
        $this->assertEquals($proUser->id, $report->reported_id);
        $this->assertCount(1, $report->evidence);
        Storage::disk('local')->assertExists($report->evidence->first()->storage_path);
    }

    public function test_user_cannot_report_themselves(): void
    {
        $user = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);
        $service = app(DisciplinaryService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No puedes reportarte a ti mismo.');

        $service->createReport($user, [
            'reported_id' => $user->id,
            'category' => ReportCategory::FRAUD->value,
            'description' => 'Test description...',
        ]);
    }

    public function test_rate_limit_on_reports(): void
    {
        $client = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL, 'status' => UserStatus::ACTIVE]);
        $service = app(DisciplinaryService::class);

        for ($i = 0; $i < 5; $i++) {
            UserReport::create([
                'reporter_id' => $client->id,
                'reported_id' => $proUser->id,
                'category' => ReportCategory::OTHER->value,
                'description' => "Reporte número $i",
                'status' => 'submitted',
            ]);
        }

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Has alcanzado el límite de reportes por hora.');

        $service->createReport($client, [
            'reported_id' => $proUser->id,
            'category' => ReportCategory::OTHER->value,
            'description' => 'Reporte 6 que debe fallar.',
        ]);
    }

    public function test_invalid_report_resolution_does_not_issue_cards(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN, 'status' => UserStatus::ACTIVE]);
        $client = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL, 'status' => UserStatus::ACTIVE]);

        $report = UserReport::create([
            'reporter_id' => $client->id,
            'reported_id' => $proUser->id,
            'category' => ReportCategory::FALSE_INFORMATION->value,
            'description' => 'Reporte infundado.',
            'status' => 'submitted',
        ]);

        $service = app(DisciplinaryService::class);
        $resolved = $service->resolveReport($report, $admin, 'invalid', notes: 'Evidencia no sustenta el reclamo.');

        $this->assertEquals('resolved_invalid', $resolved->status);
        $this->assertEquals('no_action', $resolved->resolution);
        $this->assertEquals(0, $proUser->fresh()->activeYellowCardsCount());
        $this->assertEquals(0, DisciplinaryAction::count());
    }

    public function test_valid_report_resolution_issues_yellow_card(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN, 'status' => UserStatus::ACTIVE]);
        $client = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL, 'status' => UserStatus::ACTIVE]);

        $report = UserReport::create([
            'reporter_id' => $client->id,
            'reported_id' => $proUser->id,
            'category' => ReportCategory::NO_SHOW->value,
            'description' => 'El profesional no se presentó.',
            'status' => 'submitted',
        ]);

        $service = app(DisciplinaryService::class);
        $resolved = $service->resolveReport(
            $report,
            $admin,
            'valid_yellow_card',
            reasonCode: 'no_show',
            reasonText: 'Incumplimiento de cita confirmada sin aviso previo.',
            notes: 'Capturas de chat confirmaron inasistencia.'
        );

        $this->assertEquals('resolved_valid', $resolved->status);
        $this->assertEquals('yellow_card', $resolved->resolution);
        $this->assertEquals(1, $proUser->fresh()->activeYellowCardsCount());

        $action = DisciplinaryAction::where('user_id', $proUser->id)->first();
        $this->assertNotNull($action);
        $this->assertEquals(DisciplinaryActionType::YELLOW_CARD, $action->action_type);
        $this->assertEquals(DisciplinaryActionStatus::ACTIVE, $action->status);
    }

    public function test_direct_severe_action_suspends_user_without_three_cards(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN, 'status' => UserStatus::ACTIVE]);
        $client = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL, 'status' => UserStatus::ACTIVE]);

        $report = UserReport::create([
            'reporter_id' => $client->id,
            'reported_id' => $proUser->id,
            'category' => ReportCategory::VIOLENCE->value,
            'severity_reported' => ReportSeverity::CRITICAL->value,
            'description' => 'Amenazas graves y conducta violenta.',
            'status' => 'submitted',
        ]);

        $service = app(DisciplinaryService::class);
        $resolved = $service->resolveReport(
            $report,
            $admin,
            'valid_severe',
            reasonCode: 'violence',
            reasonText: 'Conducta agresiva y amenazas registradas.',
            actionType: DisciplinaryActionType::TEMPORARY_SUSPENSION->value,
            notes: 'Suspensión directa por caso grave.',
            suspensionDays: 30
        );

        $this->assertEquals('resolved_valid', $resolved->status);
        $this->assertEquals('temporary_suspension', $resolved->resolution);
        $this->assertTrue($proUser->fresh()->isSuspended());
        $this->assertFalse($proUser->fresh()->canPerformMarketplaceActions());
    }

    public function test_appeal_acceptance_revokes_action_and_restores_status(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN, 'status' => UserStatus::ACTIVE]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL, 'status' => UserStatus::SUSPENDED]);

        $action = DisciplinaryAction::create([
            'user_id' => $proUser->id,
            'action_type' => DisciplinaryActionType::TEMPORARY_SUSPENSION,
            'severity' => ReportSeverity::HIGH,
            'reason_code' => 'misconduct',
            'reason_text' => 'Falta temporal',
            'status' => DisciplinaryActionStatus::ACTIVE,
            'issued_by_admin_id' => $admin->id,
            'issued_at' => now(),
        ]);

        $service = app(DisciplinaryService::class);

        // 1. Pro submits appeal
        $appeal = $service->submitAppeal($action, $proUser, 'Presento los comprobantes que demuestran que el cliente aceptó el cambio de horario.');
        $this->assertEquals(DisciplinaryAppealStatus::SUBMITTED, $appeal->status);

        // 2. Admin accepts appeal
        $resolvedAppeal = $service->resolveAppeal($appeal, $admin, true, 'Se verificó la evidencia y se revoca la sanción.');

        $this->assertEquals(DisciplinaryAppealStatus::ACCEPTED, $resolvedAppeal->status);
        $this->assertEquals(DisciplinaryActionStatus::REVOKED, $action->fresh()->status);
        $this->assertTrue($proUser->fresh()->isAccountActive());
        $this->assertFalse($proUser->fresh()->isSuspended());
        $this->assertEquals(0, $proUser->fresh()->activeYellowCardsCount());
    }

    public function test_involved_admin_cannot_resolve_own_case(): void
    {
        $adminA = User::factory()->create(['role' => UserRole::ADMIN, 'status' => UserStatus::ACTIVE]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL, 'status' => UserStatus::ACTIVE]);

        $report = UserReport::create([
            'reporter_id' => $adminA->id,
            'reported_id' => $proUser->id,
            'category' => ReportCategory::FRAUD->value,
            'description' => 'Admin A reportó a Pro.',
            'status' => 'submitted',
        ]);

        $service = app(DisciplinaryService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No puedes resolver un reporte en el que estás involucrado.');

        $service->resolveReport($report, $adminA, 'invalid');
    }
}
