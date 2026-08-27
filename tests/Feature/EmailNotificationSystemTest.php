<?php

namespace Tests\Feature;

use App\Enums\IdentityVerificationStatus;
use App\Enums\JobStatus;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\JobQuote;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use App\Models\User;
use App\Notifications\DirectServiceRequestedNotification;
use App\Notifications\JobCancelledNotification;
use App\Notifications\JobCompletedNotification;
use App\Notifications\JobStatusUpdatedNotification;
use App\Notifications\PaymentConfirmedClientNotification;
use App\Notifications\PaymentConfirmedProfessionalNotification;
use App\Notifications\PromotionActivatedNotification;
use App\Notifications\QuoteAcceptedNotification;
use App\Notifications\QuoteReceivedNotification;
use App\Notifications\WelcomeNotification;
use App\Services\CommerceService;
use App\Services\JobRequestService;
use App\Services\JobWorkflowService;
use App\Services\PaymentService;
use App\Services\UserRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailNotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registration_dispatches_welcome_notification(): void
    {
        Notification::fake();

        $service = app(UserRegistrationService::class);
        $user = $service->register([
            'name' => 'Juan Perez',
            'email' => 'juan@example.com',
            'phone' => '5512345678',
            'password' => 'Password123!',
            'role' => 'client',
        ]);

        Notification::assertSentTo($user, WelcomeNotification::class, function ($notification) use ($user) {
            $mail = $notification->toMail($user);
            $this->assertEquals('¡Bienvenido a Chambapp!', $mail->subject);
            return true;
        });
    }

    public function test_direct_service_request_notifies_only_service_owner_pro(): void
    {
        Notification::fake();

        $client = User::factory()->create(['role' => UserRole::CLIENT]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL]);
        $proProfile = ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);
        $otherProUser = User::factory()->create(['role' => UserRole::PROFESSIONAL]);
        $otherProProfile = ProfessionalProfile::factory()->create(['user_id' => $otherProUser->id]);

        $category = Category::factory()->create();
        $service = Service::factory()->create([
            'professional_id' => $proProfile->id,
            'category_id' => $category->id,
            'title' => 'Mantenimiento PC',
            'is_active' => true,
        ]);

        $jobRequestService = app(JobRequestService::class);
        $job = $jobRequestService->createScheduled($client, [
            'category_id' => $category->id,
            'service_id' => $service->id,
            'title' => 'Mantenimiento PC',
            'description' => 'Limpieza y pasta térmica',
            'address' => 'Av. Insurgentes 123',
            'city' => 'CDMX',
            'state' => 'CDMX',
            'postal_code' => '01000',
            'scheduled_for' => now()->addDays(2)->toDateString(),
            'scheduled_slot' => '10:00 - 12:00',
        ]);

        Notification::assertSentTo($proUser, DirectServiceRequestedNotification::class, function ($notification) use ($proUser) {
            $mail = $notification->toMail($proUser);
            $this->assertStringContainsString('Nueva solicitud de servicio en Chambapp', $mail->subject);
            return true;
        });

        Notification::assertNotSentTo($otherProUser, DirectServiceRequestedNotification::class);
    }

    public function test_new_quote_sends_quote_received_notification_with_15_percent_breakdown(): void
    {
        Notification::fake();

        $client = User::factory()->create(['role' => UserRole::CLIENT]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL]);
        $proProfile = ProfessionalProfile::factory()->create([
            'user_id' => $proUser->id,
            'verification_status' => VerificationStatus::VERIFIED,
        ]);
        $proProfile->identityVerification()->create(['status' => IdentityVerificationStatus::VERIFIED]);

        $category = Category::factory()->create();
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $proProfile->id,
            'category_id' => $category->id,
            'status' => JobStatus::PENDING,
            'title' => 'Reparación de Fuga',
        ]);

        $workflowService = app(JobWorkflowService::class);
        $quote = $workflowService->createQuote($job, $proUser, '200.00', 'Incluye materiales');

        Notification::assertSentTo($client, QuoteReceivedNotification::class, function ($notification) use ($client) {
            $mail = $notification->toMail($client);
            $this->assertStringContainsString('Nueva cotización en Chambapp', $mail->subject);
            return true;
        });
    }

    public function test_quote_accepted_notifies_professional_without_creating_checkout(): void
    {
        Notification::fake();

        $client = User::factory()->create(['role' => UserRole::CLIENT]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL]);
        $proProfile = ProfessionalProfile::factory()->create([
            'user_id' => $proUser->id,
            'verification_status' => VerificationStatus::VERIFIED,
        ]);
        $proProfile->identityVerification()->create(['status' => IdentityVerificationStatus::VERIFIED]);

        $category = Category::factory()->create();
        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $proProfile->id,
            'category_id' => $category->id,
            'status' => JobStatus::ACCEPTED,
            'title' => 'Instalación Eléctrica',
        ]);

        $quote = JobQuote::factory()->create([
            'job_request_id' => $job->id,
            'professional_id' => $proProfile->id,
            'amount' => 500.00,
            'status' => QuoteStatus::PENDING,
            'expires_at' => now()->addDays(2),
        ]);

        $workflowService = app(JobWorkflowService::class);
        $workflowService->acceptQuote($quote, $client);

        Notification::assertSentTo($proUser, QuoteAcceptedNotification::class, function ($notification) use ($proUser) {
            $mail = $notification->toMail($proUser);
            $this->assertStringContainsString('¡Cotización aceptada en Chambapp!', $mail->subject);
            return true;
        });

        $this->assertEquals(JobStatus::AWAITING_PAYMENT, $job->fresh()->status);
    }

    public function test_creating_checkout_preference_does_not_send_payment_confirmed(): void
    {
        Notification::fake();

        $client = User::factory()->create(['role' => UserRole::CLIENT]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL]);
        $proProfile = ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);

        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $proProfile->id,
            'status' => JobStatus::AWAITING_PAYMENT,
            'title' => 'Instalación Aire Acondicionado',
        ]);

        $payment = Payment::factory()->create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $proProfile->id,
            'status' => PaymentStatus::PENDING,
            'gross_amount' => 1000.00,
            'customer_total' => 1150.00,
            'professional_amount' => 850.00,
            'currency' => 'MXN',
            'provider' => 'mercadopago',
            'external_preference_id' => 'mp-pref-12345',
        ]);

        Notification::assertNotSentTo($client, PaymentConfirmedClientNotification::class);
        Notification::assertNotSentTo($proUser, PaymentConfirmedProfessionalNotification::class);
    }

    public function test_payment_approval_dispatches_client_and_pro_payment_confirmed_notifications(): void
    {
        Notification::fake();

        $client = User::factory()->create(['role' => UserRole::CLIENT]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL]);
        $proProfile = ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);

        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $proProfile->id,
            'status' => JobStatus::AWAITING_PAYMENT,
            'title' => 'Pintura Interior',
        ]);

        $proProfile->update(['mercadopago_user_id' => 'mp-pro-12345']);
        $payment = Payment::factory()->create([
            'job_request_id' => $job->id,
            'client_id' => $client->id,
            'professional_id' => $proProfile->id,
            'status' => PaymentStatus::PENDING,
            'gross_amount' => 500.00,
            'customer_total' => 575.00,
            'professional_amount' => 425.00,
            'currency' => 'MXN',
            'provider' => 'mercadopago',
            'external_reference' => 'ref-test-pay-123',
        ]);

        $paymentService = app(PaymentService::class);
        $paymentService->applyProviderPayment($payment, [
            'id' => 'mp-pay-999',
            'status' => 'approved',
            'external_reference' => 'ref-test-pay-123',
            'transaction_amount' => '500.00',
            'currency_id' => 'MXN',
            'live_mode' => false,
            'collector_id' => 'mp-pro-12345',
        ], 'evt-test-1', ['raw' => 'audit']);

        Notification::assertSentTo($client, PaymentConfirmedClientNotification::class, function ($notification) use ($client) {
            $mail = $notification->toMail($client);
            $this->assertStringContainsString('Pago confirmado', $mail->subject);
            return true;
        });

        Notification::assertSentTo($proUser, PaymentConfirmedProfessionalNotification::class, function ($notification) use ($proUser) {
            $mail = $notification->toMail($proUser);
            $this->assertStringContainsString('El cliente realizó el pago', $mail->subject);
            return true;
        });

        $this->assertEquals(JobStatus::PAID, $job->fresh()->status);
    }

    public function test_operational_status_updates_notify_client(): void
    {
        Notification::fake();

        $client = User::factory()->create(['role' => UserRole::CLIENT]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL]);
        $proProfile = ProfessionalProfile::factory()->create([
            'user_id' => $proUser->id,
            'verification_status' => VerificationStatus::VERIFIED,
        ]);
        $proProfile->identityVerification()->create(['status' => IdentityVerificationStatus::VERIFIED]);

        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $proProfile->id,
            'status' => JobStatus::PAID,
            'title' => 'Reparación de Techo',
        ]);

        $workflowService = app(JobWorkflowService::class);
        $workflowService->onTheWay($job);

        Notification::assertSentTo($client, JobStatusUpdatedNotification::class, function ($notification) {
            return $notification->notificationType === 'job_on_the_way';
        });

        $workflowService->arrive($job->fresh());

        Notification::assertSentTo($client, JobStatusUpdatedNotification::class, function ($notification) {
            return $notification->notificationType === 'job_arrived';
        });
    }

    public function test_job_completed_sends_review_invitation_to_client(): void
    {
        Notification::fake();

        $client = User::factory()->create(['role' => UserRole::CLIENT]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL]);
        $proProfile = ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);

        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $proProfile->id,
            'status' => JobStatus::AWAITING_CONFIRMATION,
            'completion_code' => '123456',
            'completion_code_expires_at' => now()->addDay(),
            'title' => 'Plomería',
        ]);

        $workflowService = app(JobWorkflowService::class);
        $workflowService->confirmCompletion($job, '123456');

        Notification::assertSentTo($client, JobCompletedNotification::class, function ($notification) use ($client) {
            $mail = $notification->toMail($client);
            $this->assertStringContainsString('Chamba completada', $mail->subject);
            return true;
        });
    }

    public function test_job_cancellation_notifies_affected_party(): void
    {
        Notification::fake();

        $client = User::factory()->create(['role' => UserRole::CLIENT]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL]);
        $proProfile = ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);

        $job = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $proProfile->id,
            'status' => JobStatus::PENDING,
            'title' => 'Jardinería',
        ]);

        $workflowService = app(JobWorkflowService::class);
        $workflowService->cancel($job, 'Ya no necesito el servicio', $client);

        Notification::assertSentTo($proUser, JobCancelledNotification::class, function ($notification) use ($proUser) {
            $mail = $notification->toMail($proUser);
            $this->assertStringContainsString('Cancelación de chamba', $mail->subject);
            return true;
        });
    }

    public function test_promotion_fulfillment_sends_promotion_activated_notification(): void
    {
        Notification::fake();

        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL]);
        $proProfile = ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);
        $category = Category::factory()->create();
        $service = Service::factory()->create([
            'professional_id' => $proProfile->id,
            'category_id' => $category->id,
            'title' => 'Cerrajería 24/7',
        ]);

        $commerceService = app(CommerceService::class);
        $order = $commerceService->createFeaturedOrder($proProfile, $service, 7);
        $commerceService->applyPaidOrder($order);

        Notification::assertSentTo($proUser, PromotionActivatedNotification::class, function ($notification) use ($proUser) {
            $mail = $notification->toMail($proUser);
            $this->assertStringContainsString('¡Tu promoción está activa!', $mail->subject);
            return true;
        });
    }
}
