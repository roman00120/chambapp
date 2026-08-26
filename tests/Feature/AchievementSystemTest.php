<?php

namespace Tests\Feature;

use App\Enums\AchievementLevel;
use App\Enums\IdentityVerificationStatus;
use App\Enums\JobStatus;
use App\Enums\ReportCategory;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Category;
use App\Models\JobRequest;
use App\Models\ProfessionalIdentityVerification;
use App\Models\ProfessionalProfile;
use App\Models\Review;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserReport;
use App\Services\AchievementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_experience_progression_levels(): void
    {
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL, 'status' => UserStatus::ACTIVE]);
        $profile = ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);
        $client = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);
        $category = Category::factory()->create();

        $service = app(AchievementService::class);

        // 4 trabajos -> sin medalla
        for ($i = 0; $i < 4; $i++) {
            JobRequest::factory()->create([
                'client_id' => $client->id,
                'professional_id' => $profile->id,
                'category_id' => $category->id,
                'status' => JobStatus::COMPLETED,
            ]);
        }
        $res = $service->recalculateForProfessional($profile);
        $this->assertNull($res['experience']);

        // 5 trabajos -> Bronce
        JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $profile->id,
            'category_id' => $category->id,
            'status' => JobStatus::COMPLETED,
        ]);
        $res = $service->recalculateForProfessional($profile);
        $this->assertEquals(AchievementLevel::BRONZE, $res['experience']->level);

        // 10 trabajos -> Plata
        for ($i = 0; $i < 5; $i++) {
            JobRequest::factory()->create([
                'client_id' => $client->id,
                'professional_id' => $profile->id,
                'category_id' => $category->id,
                'status' => JobStatus::COMPLETED,
            ]);
        }
        $res = $service->recalculateForProfessional($profile);
        $this->assertEquals(AchievementLevel::SILVER, $res['experience']->level);

        // 25 trabajos -> Oro
        for ($i = 0; $i < 15; $i++) {
            JobRequest::factory()->create([
                'client_id' => $client->id,
                'professional_id' => $profile->id,
                'category_id' => $category->id,
                'status' => JobStatus::COMPLETED,
            ]);
        }
        $res = $service->recalculateForProfessional($profile);
        $this->assertEquals(AchievementLevel::GOLD, $res['experience']->level);

        // 50 trabajos -> Diamante
        for ($i = 0; $i < 25; $i++) {
            JobRequest::factory()->create([
                'client_id' => $client->id,
                'professional_id' => $profile->id,
                'category_id' => $category->id,
                'status' => JobStatus::COMPLETED,
            ]);
        }
        $res = $service->recalculateForProfessional($profile);
        $this->assertEquals(AchievementLevel::DIAMOND, $res['experience']->level);
    }

    public function test_cancelled_and_self_contracted_jobs_do_not_count(): void
    {
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL, 'status' => UserStatus::ACTIVE]);
        $profile = ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);
        $client = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);
        $category = Category::factory()->create();

        $service = app(AchievementService::class);

        // 4 trabajos completados legítimos
        for ($i = 0; $i < 4; $i++) {
            JobRequest::factory()->create([
                'client_id' => $client->id,
                'professional_id' => $profile->id,
                'category_id' => $category->id,
                'status' => JobStatus::COMPLETED,
            ]);
        }

        // 2 trabajos cancelados
        JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $profile->id,
            'category_id' => $category->id,
            'status' => JobStatus::CANCELLED,
        ]);
        JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $profile->id,
            'category_id' => $category->id,
            'status' => JobStatus::CANCELLED_BY_CLIENT,
        ]);

        // 1 auto-contratación completada
        JobRequest::factory()->create([
            'client_id' => $proUser->id,
            'professional_id' => $profile->id,
            'category_id' => $category->id,
            'status' => JobStatus::COMPLETED,
        ]);

        // Total legítimo es 4 -> No debe alcanzar Bronce
        $res = $service->recalculateForProfessional($profile);
        $this->assertNull($res['experience']);
    }

    public function test_excellent_service_criteria(): void
    {
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL, 'status' => UserStatus::ACTIVE]);
        $profile = ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);
        $client = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);
        $category = Category::factory()->create();

        $service = app(AchievementService::class);

        // 4 reseñas de 5 estrellas -> Sin medalla (mínimo 5 requerido)
        for ($i = 0; $i < 4; $i++) {
            $job = JobRequest::factory()->create([
                'client_id' => $client->id,
                'professional_id' => $profile->id,
                'category_id' => $category->id,
                'status' => JobStatus::COMPLETED,
            ]);
            Review::create([
                'job_request_id' => $job->id,
                'client_id' => $client->id,
                'professional_id' => $profile->id,
                'rating' => 5,
                'comment' => 'Excelente servicio '.$i,
                'is_hidden' => false,
            ]);
        }
        $res = $service->recalculateForProfessional($profile);
        $this->assertNull($res['excellent_service']);

        // 5ta reseña de 5 estrellas -> Bronce
        $job5 = JobRequest::factory()->create([
            'client_id' => $client->id,
            'professional_id' => $profile->id,
            'category_id' => $category->id,
            'status' => JobStatus::COMPLETED,
        ]);
        Review::create([
            'job_request_id' => $job5->id,
            'client_id' => $client->id,
            'professional_id' => $profile->id,
            'rating' => 5,
            'comment' => 'Muy recomendado',
            'is_hidden' => false,
        ]);

        $res = $service->recalculateForProfessional($profile);
        $this->assertEquals(AchievementLevel::BRONZE, $res['excellent_service']->level);
    }

    public function test_top_pro_requires_kyc_and_high_volume(): void
    {
        config(['chambapp.identity_verification.required' => true]);

        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL, 'status' => UserStatus::ACTIVE]);
        $profile = ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);
        $client = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);
        $category = Category::factory()->create();

        $service = app(AchievementService::class);

        // 25 trabajos completados y 10 reseñas 5 estrellas
        for ($i = 0; $i < 25; $i++) {
            $job = JobRequest::factory()->create([
                'client_id' => $client->id,
                'professional_id' => $profile->id,
                'category_id' => $category->id,
                'status' => JobStatus::COMPLETED,
            ]);
            if ($i < 10) {
                Review::create([
                    'job_request_id' => $job->id,
                    'client_id' => $client->id,
                    'professional_id' => $profile->id,
                    'rating' => 5,
                    'comment' => 'Excelente trabajo',
                    'is_hidden' => false,
                ]);
            }
        }

        // Sin KYC aprobado -> Top Pro no se otorga
        $res = $service->recalculateForProfessional($profile);
        $this->assertNull($res['top_pro']);

        // Aprobando KYC
        ProfessionalIdentityVerification::create([
            'professional_id' => $profile->id,
            'verification_provider' => 'didit',
            'status' => IdentityVerificationStatus::VERIFIED,
            'verified_at' => now(),
        ]);

        $resWithKyc = $service->recalculateForProfessional($profile->fresh());
        $this->assertEquals(AchievementLevel::DIAMOND, $resWithKyc['top_pro']->level);
    }

    public function test_good_client_achievement_for_clients(): void
    {
        $client = User::factory()->create(['role' => UserRole::CLIENT, 'status' => UserStatus::ACTIVE]);
        $proUser = User::factory()->create(['role' => UserRole::PROFESSIONAL, 'status' => UserStatus::ACTIVE]);
        $profile = ProfessionalProfile::factory()->create(['user_id' => $proUser->id]);
        $category = Category::factory()->create();

        $service = app(AchievementService::class);

        // 5 trabajos contratados y completados -> Buen Cliente Bronce
        for ($i = 0; $i < 5; $i++) {
            JobRequest::factory()->create([
                'client_id' => $client->id,
                'professional_id' => $profile->id,
                'category_id' => $category->id,
                'status' => JobStatus::COMPLETED,
            ]);
        }

        $res = $service->recalculateForClient($client);
        $this->assertEquals(AchievementLevel::BRONZE, $res['good_client']->level);
    }
}
