<?php

namespace Tests\Feature;

use App\Enums\IdentityVerificationStatus;
use App\Enums\JobStatus;
use App\Enums\PaymentKind;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Category;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\ProfessionalIdentityVerification;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use App\Models\User;
use App\Services\MercadoPagoService;
use App\Services\PaymentCalculationService;
use App\Services\ProfessionalIdentityVerificationService;
use App\Services\ReviewService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiModeUserCapabilitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_has_multi_mode_capabilities(): void
    {
        $admin = User::factory()->create([
            'email' => 'gerawx@gmail.com',
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE,
        ]);

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->canActAsClient());
        $this->assertTrue($admin->canActAsProfessional());
        $this->assertEquals(['client', 'professional', 'admin'], $admin->capabilities());
    }

    public function test_client_and_professional_have_respective_capabilities(): void
    {
        $client = User::factory()->create([
            'role' => UserRole::CLIENT,
            'status' => UserStatus::ACTIVE,
        ]);
        $this->assertFalse($client->isAdmin());
        $this->assertTrue($client->canActAsClient());
        $this->assertFalse($client->canActAsProfessional());

        $proUser = User::factory()->create([
            'role' => UserRole::PROFESSIONAL,
            'status' => UserStatus::ACTIVE,
        ]);
        $profile = ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);

        $this->assertFalse($proUser->isAdmin());
        $this->assertTrue($proUser->canActAsClient());
        $this->assertTrue($proUser->canActAsProfessional());
    }

    public function test_web_mode_switch_updates_session(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->actingAs($admin)->post('/modo-activo', ['mode' => 'professional']);
        $response->assertRedirect(route('professional.dashboard'));
        $this->assertEquals('professional', session('active_mode'));

        $response = $this->actingAs($admin)->post('/modo-activo', ['mode' => 'client']);
        $response->assertRedirect(route('client.dashboard'));
        $this->assertEquals('client', session('active_mode'));
    }

    public function test_api_active_mode_endpoint_and_user_resource(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/auth/active-mode', ['mode' => 'professional']);
        $response->assertOk();
        $response->assertJsonPath('data.active_mode', 'professional');
        $response->assertJsonPath('data.user.capabilities', ['client', 'professional', 'admin']);

        $meResponse = $this->actingAs($admin, 'sanctum')->withHeader('X-Chambapp-Mode', 'professional')->getJson('/api/v1/me');
        $meResponse->assertOk();
        $meResponse->assertJsonPath('data.active_mode', 'professional');
    }

    public function test_anti_self_contracting_prevents_requesting_own_service(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE,
        ]);
        $profile = ProfessionalProfile::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create();
        $service = Service::factory()->create([
            'professional_id' => $profile->id,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $serviceJob = app(\App\Services\JobRequestService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No puedes solicitar un servicio a tu propio perfil profesional.');

        $serviceJob->createScheduled($user, [
            'service_id' => $service->id,
            'category_id' => $category->id,
            'service_mode' => 'scheduled',
            'title' => 'Trabajo de prueba',
            'description' => 'Descripción de prueba',
            'address' => 'Av. Insurgentes 123',
            'city' => 'CDMX',
            'state' => 'CDMX',
            'postal_code' => '06700',
            'scheduled_for' => now()->addDay(),
            'scheduled_slot' => 'morning',
        ]);
    }

    public function test_anti_self_payment_prevents_mp_preference(): void
    {
        $user = User::factory()->create([
            'email' => 'gerawx@gmail.com',
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE,
        ]);
        $profile = ProfessionalProfile::factory()->create([
            'user_id' => $user->id,
            'mercadopago_access_token' => 'fake_token',
        ]);
        $category = Category::factory()->create();
        $job = JobRequest::factory()->create([
            'client_id' => $user->id,
            'professional_id' => $profile->id,
            'category_id' => $category->id,
            'status' => JobStatus::AWAITING_PAYMENT,
            'agreed_price' => '1000.00',
        ]);
        $payment = Payment::factory()->create([
            'job_request_id' => $job->id,
            'professional_id' => $profile->id,
            'client_id' => $user->id,
            'gross_amount' => '1150.00',
            'status' => PaymentStatus::PENDING,
        ]);

        $mp = app(MercadoPagoService::class);

        $this->expectException(\App\Exceptions\MercadoPagoException::class);
        $this->expectExceptionMessage('No está permitido realizar pagos entre la misma cuenta de cliente y profesional.');

        $mp->createPreference($payment);
    }

    public function test_anti_self_review_prevents_rating_own_job(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE,
        ]);
        $profile = ProfessionalProfile::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create();
        $job = JobRequest::factory()->create([
            'client_id' => $user->id,
            'professional_id' => $profile->id,
            'category_id' => $category->id,
            'status' => JobStatus::COMPLETED,
        ]);

        $reviewService = app(ReviewService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No está permitido calificar tus propios servicios.');

        $reviewService->create($job, $user, 5, 'Excelente');
    }

    public function test_admin_acting_as_pro_cannot_bypass_kyc_when_required(): void
    {
        config(['chambapp.identity_verification.required' => true]);

        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE,
        ]);
        $profile = ProfessionalProfile::factory()->create(['user_id' => $admin->id]);

        $kycService = app(ProfessionalIdentityVerificationService::class);

        // Sin verificación aprobada
        $this->assertFalse($kycService->professionalCanAcceptJobs($profile));
        $this->assertFalse($kycService->hasVerifiedIdentity($profile));

        // Con verificación aprobada
        ProfessionalIdentityVerification::create([
            'professional_id' => $profile->id,
            'verification_provider' => 'didit',
            'status' => IdentityVerificationStatus::VERIFIED,
            'verified_at' => now(),
        ]);

        $this->assertTrue($kycService->professionalCanAcceptJobs($profile->fresh()));
    }
}
