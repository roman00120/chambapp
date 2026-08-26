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
use App\Models\ProfessionalProfile;
use App\Models\User;
use App\Models\UserReport;
use App\Services\DisciplinaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisciplinaryClientAndProTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_report_professional_and_pro_receives_card(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN, 'status' => UserStatus::ACTIVE]);
        $client = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL, 'status' => UserStatus::ACTIVE]);
        ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);

        $service = app(DisciplinaryService::class);

        // Cliente reporta a Profesional
        $report = $service->createReport($client, [
            'reported_id' => $proUser->id,
            'category' => ReportCategory::NO_SHOW->value,
            'description' => 'El profesional no llegó a la cita programada.',
        ]);
        $this->assertEquals('submitted', $report->status);

        // Admin resuelve como válido
        $service->resolveReport($report, $admin, 'valid_yellow_card', reasonCode: 'no_show', reasonText: 'Inasistencia sin previo aviso');
        $this->assertEquals(1, $proUser->fresh()->activeYellowCardsCount());
    }

    public function test_professional_can_report_client_and_client_receives_card(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN, 'status' => UserStatus::ACTIVE]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL, 'status' => UserStatus::ACTIVE]);
        ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);
        $client = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);

        $service = app(DisciplinaryService::class);

        // Profesional reporta a Cliente (sin tener perfil profesional el cliente)
        $report = $service->createReport($proUser, [
            'reported_id' => $client->id,
            'category' => ReportCategory::ABUSIVE_BEHAVIOR->value,
            'description' => 'El cliente usó lenguaje ofensivo e insultos.',
        ]);
        $this->assertEquals('submitted', $report->status);

        // Admin resuelve como válido -> Cliente recibe tarjeta amarilla asociada a su user_id
        $resolved = $service->resolveReport(
            $report,
            $admin,
            'valid_yellow_card',
            reasonCode: 'abusive_comm',
            reasonText: 'Lenguaje inapropiado hacia el prestador del servicio'
        );
        $this->assertEquals('resolved_valid', $resolved->status);
        $this->assertEquals(1, $client->fresh()->activeYellowCardsCount());
    }

    public function test_multimode_user_retains_cards_across_modes_without_bypass(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN, 'status' => UserStatus::ACTIVE]);
        $multiUser = User::factory()->create([
            'email' => 'gerawx@gmail.com',
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE,
        ]);
        ProfessionalProfile::factory()->create(['user_id' => $multiUser->id]);

        $reporter = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);
        $service = app(DisciplinaryService::class);

        // Se emite tarjeta amarilla
        $action = DisciplinaryAction::create([
            'user_id' => $multiUser->id,
            'action_type' => DisciplinaryActionType::YELLOW_CARD,
            'severity' => ReportSeverity::MEDIUM,
            'reason_code' => 'policy_warning',
            'reason_text' => 'Advertencia por falta administrativa',
            'status' => DisciplinaryActionStatus::ACTIVE,
            'issued_by_admin_id' => $admin->id,
            'issued_at' => now(),
        ]);

        // La tarjeta pertenece a user_id y se mantiene activa en cualquier modo
        $this->assertEquals(1, $multiUser->fresh()->activeYellowCardsCount());
        $this->assertEquals('client', $multiUser->resolveActiveMode('client'));
        $this->assertEquals(1, $multiUser->fresh()->activeYellowCardsCount());
        $this->assertEquals('professional', $multiUser->resolveActiveMode('professional'));
        $this->assertEquals(1, $multiUser->fresh()->activeYellowCardsCount());
    }

    public function test_client_can_appeal_and_accepted_appeal_revokes_card(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN, 'status' => UserStatus::ACTIVE]);
        $client = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);

        $action = DisciplinaryAction::create([
            'user_id' => $client->id,
            'action_type' => DisciplinaryActionType::YELLOW_CARD,
            'severity' => ReportSeverity::LOW,
            'reason_code' => 'misunderstanding',
            'reason_text' => 'Falta de comunicación',
            'status' => DisciplinaryActionStatus::ACTIVE,
            'issued_by_admin_id' => $admin->id,
            'issued_at' => now(),
        ]);

        $service = app(DisciplinaryService::class);

        // Cliente apela
        $appeal = $service->submitAppeal($action, $client, 'Aclaración de lo ocurrido con evidencia de respaldo');
        $this->assertEquals(DisciplinaryAppealStatus::SUBMITTED, $appeal->status);

        // Admin acepta apelación -> Tarjeta revocada y contador en 0
        $resolved = $service->resolveAppeal($appeal, $admin, true, 'Apelación aceptada');
        $this->assertEquals(DisciplinaryAppealStatus::ACCEPTED, $resolved->status);
        $this->assertEquals(DisciplinaryActionStatus::REVOKED, $action->fresh()->status);
        $this->assertEquals(0, $client->fresh()->activeYellowCardsCount());
    }

    public function test_global_suspension_blocks_both_client_and_pro_actions(): void
    {
        $suspendedUser = User::factory()->create([
            'role' => UserRole::CLIENT,
            'status' => UserStatus::SUSPENDED,
        ]);
        $profile = ProfessionalProfile::factory()->create(['user_id' => $suspendedUser->id]);

        $this->assertFalse($suspendedUser->canPerformMarketplaceActions());
        $this->assertFalse($suspendedUser->canActAsClient());
        $this->assertFalse($suspendedUser->canActAsProfessional());

        // Intentar cambiar modo no permite bypass
        $this->assertFalse($suspendedUser->isActive());
    }
}
