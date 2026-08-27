<?php

namespace Tests\Feature;

use App\Enums\JobDisputeStatus;
use App\Enums\JobStatus;
use App\Enums\PaymentStatus;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\JobDispute;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use App\Models\Report;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControlPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_access_admin_area(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->client()->create())->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs(User::factory()->professional()->create())->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'))->assertOk()->assertSee('Dashboard administrativo');
    }

    public function test_dashboard_uses_real_counts_and_historical_payment_fields(): void
    {
        $admin = User::factory()->admin()->create();
        $professional = ProfessionalProfile::factory()->create();
        $client = User::factory()->client()->create();
        $job = JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => $professional->id, 'status' => JobStatus::COMPLETED]);
        Payment::factory()->create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'gross_amount' => '1000.00',
            'platform_fee_percent' => '10.00',
            'platform_fee' => '100.00',
            'professional_amount' => '900.00',
            'status' => PaymentStatus::APPROVED,
            'paid_at' => now(),
        ]);
        Payment::factory()->create([
            'job_request_id' => JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => $professional->id])->id,
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'gross_amount' => '500.00',
            'platform_fee_percent' => '15.00',
            'platform_fee' => '75.00',
            'professional_amount' => '425.00',
            'status' => PaymentStatus::APPROVED,
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['range' => '30']));
        $response->assertOk()->assertSee('1,500.00')->assertSee('175.00');
        $response->assertViewHas('stats', fn (array $stats): bool => $stats['approvedPayments'] === 2 && (float) $stats['fees'] === 175.0);
        $this->actingAs($admin)->get(route('admin.commissions.index', ['range' => '30']))->assertOk()->assertSee('10.00%')->assertSee('100.00');
    }

    public function test_admin_can_search_and_change_user_state_with_audit_without_deleting_history(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->client()->create(['name' => 'Cliente Auditable', 'email' => 'audit@example.test']);
        $job = JobRequest::factory()->create(['client_id' => $user->id]);

        $this->actingAs($admin)->get(route('admin.users.index', ['q' => 'audit@example.test']))->assertOk()->assertSee('Cliente Auditable');
        $this->actingAs($admin)->patch(route('admin.users.status', $user), ['status' => 'suspended'])->assertRedirect();
        $this->assertSame('suspended', $user->fresh()->status->value);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'user.status_changed', 'subject_id' => $user->id]);
        $this->assertDatabaseHas('job_requests', ['id' => $job->id]);
        $this->actingAs($admin)->patch(route('admin.users.status', $admin), ['status' => 'blocked'])->assertSessionHasErrors('user');
    }

    public function test_all_user_status_buttons_apply_the_selected_state(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->professional()->create();

        foreach (['suspended', 'blocked', 'active'] as $status) {
            $this->actingAs($admin)
                ->patch(route('admin.users.status', $user), ['status' => $status])
                ->assertRedirect()
                ->assertSessionHas('status');

            $this->assertSame($status, $user->fresh()->status->value);
        }
    }

    public function test_admin_can_verify_professional_and_moderate_service_and_category(): void
    {
        $admin = User::factory()->admin()->create();
        $professional = ProfessionalProfile::factory()->create(['verification_status' => VerificationStatus::PENDING]);
        $category = Category::factory()->create();
        $service = Service::factory()->create(['professional_id' => $professional->id, 'category_id' => $category->id]);

        $this->actingAs($admin)->post(route('admin.professionals.approve', $professional))->assertRedirect();
        $this->assertSame(VerificationStatus::VERIFIED, $professional->fresh()->verification_status);
        $this->assertSame($admin->id, $professional->fresh()->verified_by);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'professional.verification_verified']);

        $this->actingAs($admin)->patch(route('admin.services.moderate', $service), ['action' => 'deactivate'])->assertRedirect();
        $this->assertFalse($service->fresh()->is_active);
        $this->actingAs($admin)->get(route('marketplace.service', $service))->assertNotFound();
        $this->actingAs($admin)->patch(route('admin.categories.toggle', $category))->assertRedirect();
        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_professional_approval_and_rejection_buttons_change_verification(): void
    {
        $admin = User::factory()->admin()->create();
        $professional = ProfessionalProfile::factory()->create([
            'verification_status' => VerificationStatus::UNVERIFIED,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.professionals.approve', $professional))
            ->assertRedirect()
            ->assertSessionHas('status');
        $this->assertSame(VerificationStatus::VERIFIED, $professional->fresh()->verification_status);

        $this->actingAs($admin)
            ->post(route('admin.professionals.reject', $professional), ['reason' => 'Documento pendiente'])
            ->assertRedirect()
            ->assertSessionHas('status');
        $this->assertSame(VerificationStatus::REJECTED, $professional->fresh()->verification_status);
        $this->assertSame('Documento pendiente', $professional->fresh()->verification_rejection_reason);
    }

    public function test_admin_index_pages_are_operational(): void
    {
        $admin = User::factory()->admin()->create();

        foreach ([
            'admin.dashboard',
            'admin.users.index',
            'admin.professionals.index',
            'admin.categories.index',
            'admin.services.index',
            'admin.jobs.index',
            'admin.payments.index',
            'admin.commissions.index',
            'admin.reports.index',
            'admin.reviews.index',
            'admin.disputes.index',
        ] as $routeName) {
            $this->actingAs($admin)->get(route($routeName))->assertOk();
        }
    }

    public function test_admin_moderates_review_and_recalculates_rating(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        $job = JobRequest::factory()->completed()->create(['client_id' => $client->id, 'professional_id' => $professional->id]);
        $review = Review::create(['job_request_id' => $job->id, 'client_id' => $client->id, 'professional_id' => $professional->id, 'rating' => 1, 'comment' => 'Mala experiencia válida.']);

        $this->actingAs($admin)->patch(route('admin.reviews.moderate', $review), ['action' => 'hide', 'reason' => 'Contiene datos personales'])->assertRedirect();
        $this->assertTrue($review->fresh()->is_hidden);
        $this->assertSame('0.00', $professional->fresh()->average_rating);
        $this->assertSame(0, $professional->fresh()->total_reviews);
        $this->actingAs($admin)->patch(route('admin.reviews.moderate', $review), ['action' => 'restore'])->assertRedirect();
        $this->assertFalse($review->fresh()->is_hidden);
        $this->assertSame('1.00', $professional->fresh()->average_rating);
    }

    public function test_admin_resolves_report_and_dispute_without_changing_payment(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->client()->create();
        $professional = ProfessionalProfile::factory()->create();
        $job = JobRequest::factory()->create(['client_id' => $client->id, 'professional_id' => $professional->id, 'status' => JobStatus::DISPUTED]);
        $payment = Payment::factory()->create(['job_request_id' => $job->id, 'client_id' => $client->id, 'professional_id' => $professional->id, 'status' => PaymentStatus::APPROVED, 'gross_amount' => '700.00', 'platform_fee' => '105.00']);
        $dispute = JobDispute::create(['job_request_id' => $job->id, 'opened_by' => $client->id, 'reason' => 'other', 'status' => JobDisputeStatus::OPEN]);
        $report = \App\Models\UserReport::create([
            'reporter_id' => $client->id,
            'reported_id' => $professional->user_id,
            'category' => 'abusive_behavior',
            'description' => 'Comportamiento no profesional.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->patch(route('admin.disputes.status', $dispute), ['status' => 'reviewing'])->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.disputes.status', $dispute), ['status' => 'resolved'])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.reports.resolve', $report), [
            'decision' => 'invalid',
            'reason_code' => 'insufficient_evidence',
            'reason_text' => 'Evidencia insuficiente.',
        ])->assertRedirect();
        $this->assertSame('resolved', $dispute->fresh()->status->value);
        $this->assertNotNull($dispute->fresh()->resolved_at);
        $this->assertSame('resolved_invalid', $report->fresh()->status);
        $this->assertSame('approved', $payment->fresh()->status->value);
        $this->assertSame('105.00', $payment->fresh()->platform_fee);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'dispute.resolved']);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'disciplinary.report.resolved']);
    }
}
