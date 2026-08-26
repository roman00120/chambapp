<?php

namespace App\Services;

use App\Enums\AchievementLevel;
use App\Enums\DisciplinaryActionStatus;
use App\Enums\DisciplinaryActionType;
use App\Enums\JobStatus;
use App\Enums\ReportCategory;
use App\Models\Achievement;
use App\Models\DisciplinaryAction;
use App\Models\JobRequest;
use App\Models\ProfessionalProfile;
use App\Models\Review;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserReport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    public function seedStandardAchievements(): void
    {
        $standards = [
            [
                'code' => 'experience',
                'name' => 'Experiencia',
                'description' => 'Reconocimiento otorgado por completar exitosamente trabajos en Chambapp.',
                'category' => 'merit',
                'audience' => 'professional',
                'icon' => 'trophy',
                'is_public' => true,
                'is_active' => true,
            ],
            [
                'code' => 'excellent_service',
                'name' => 'Excelente servicio',
                'description' => 'Reconocimiento a profesionales con altas calificaciones y satisfacción de clientes confirmada.',
                'category' => 'merit',
                'audience' => 'professional',
                'icon' => 'star-fill',
                'is_public' => true,
                'is_active' => true,
            ],
            [
                'code' => 'reliable_pro',
                'name' => 'Profesional confiable',
                'description' => 'Otorgado a profesionales con puntualidad, cumplimiento y cero inasistencias en sus servicios.',
                'category' => 'trust',
                'audience' => 'professional',
                'icon' => 'shield-check',
                'is_public' => true,
                'is_active' => true,
            ],
            [
                'code' => 'top_pro',
                'name' => 'Profesional destacado',
                'description' => 'Distinción exclusiva para profesionales de alto volumen, identidad verificada y reputación sobresaliente.',
                'category' => 'merit',
                'audience' => 'professional',
                'icon' => 'gem',
                'is_public' => true,
                'is_active' => true,
            ],
            [
                'code' => 'good_client',
                'name' => 'Buen cliente',
                'description' => 'Reconocimiento a clientes activos con excelente historial de contrataciones en la plataforma.',
                'category' => 'client',
                'audience' => 'client',
                'icon' => 'heart-fill',
                'is_public' => true,
                'is_active' => true,
            ],
        ];

        foreach ($standards as $std) {
            Achievement::firstOrCreate(['code' => $std['code']], $std);
        }
    }

    public function recalculateForProfessional(ProfessionalProfile $profile): array
    {
        $this->seedStandardAchievements();
        $user = $profile->user;
        if (! $user) {
            return [];
        }

        // 1. Trabajos válidos completados (excluyendo auto-contrataciones)
        $completedJobsCount = JobRequest::query()
            ->where('professional_id', $profile->id)
            ->where('client_id', '!=', $profile->user_id)
            ->where('status', JobStatus::COMPLETED->value)
            ->count();

        // 2. Reseñas visibles válidas
        $reviewsQuery = Review::query()
            ->where('professional_id', $profile->id)
            ->where('client_id', '!=', $profile->user_id)
            ->where('is_hidden', false);

        $validReviewsCount = $reviewsQuery->count();
        $avgRating = $validReviewsCount > 0 ? (float) $reviewsQuery->avg('rating') : 0.0;

        // 3. Verificación disciplinaria y reportes
        $isSuspended = $user->isSuspended() || $user->isBanned();
        $hasNoShowReport = UserReport::query()
            ->where('reported_id', $user->id)
            ->where('category', ReportCategory::NO_SHOW->value)
            ->where('status', 'resolved_valid')
            ->exists();

        $hasActiveSevereAction = DisciplinaryAction::query()
            ->where('user_id', $user->id)
            ->where('status', DisciplinaryActionStatus::ACTIVE->value)
            ->whereIn('action_type', [
                DisciplinaryActionType::TEMPORARY_SUSPENSION->value,
                DisciplinaryActionType::INDEFINITE_SUSPENSION->value,
                DisciplinaryActionType::BAN->value,
            ])
            ->exists();

        $isVerifiedKYC = $profile->hasVerifiedIdentity();

        $results = [];

        // A. Experiencia (5 bronze, 10 silver, 25 gold, 50 diamond, 100 master)
        $expLevel = null;
        if ($completedJobsCount >= 100) {
            $expLevel = AchievementLevel::MASTER;
        } elseif ($completedJobsCount >= 50) {
            $expLevel = AchievementLevel::DIAMOND;
        } elseif ($completedJobsCount >= 25) {
            $expLevel = AchievementLevel::GOLD;
        } elseif ($completedJobsCount >= 10) {
            $expLevel = AchievementLevel::SILVER;
        } elseif ($completedJobsCount >= 5) {
            $expLevel = AchievementLevel::BRONZE;
        }
        $results['experience'] = $this->syncUserAchievement($user, 'experience', $expLevel, [
            'completed_jobs' => $completedJobsCount,
        ]);

        // B. Excelente servicio (5 @ >=4.5, 10 @ >=4.6, 25 @ >=4.7, 50 @ >=4.8)
        $serviceLevel = null;
        if ($validReviewsCount >= 50 && $avgRating >= 4.8) {
            $serviceLevel = AchievementLevel::DIAMOND;
        } elseif ($validReviewsCount >= 25 && $avgRating >= 4.7) {
            $serviceLevel = AchievementLevel::GOLD;
        } elseif ($validReviewsCount >= 10 && $avgRating >= 4.6) {
            $serviceLevel = AchievementLevel::SILVER;
        } elseif ($validReviewsCount >= 5 && $avgRating >= 4.5) {
            $serviceLevel = AchievementLevel::BRONZE;
        }
        $results['excellent_service'] = $this->syncUserAchievement($user, 'excellent_service', $serviceLevel, [
            'valid_reviews' => $validReviewsCount,
            'avg_rating' => round($avgRating, 2),
        ]);

        // C. Profesional confiable (>= 10 jobs, no no-show, not suspended, no severe action)
        $reliableLevel = null;
        if ($completedJobsCount >= 10 && ! $hasNoShowReport && ! $isSuspended && ! $hasActiveSevereAction) {
            $reliableLevel = AchievementLevel::GOLD;
        }
        $results['reliable_pro'] = $this->syncUserAchievement($user, 'reliable_pro', $reliableLevel, [
            'completed_jobs' => $completedJobsCount,
        ]);

        // D. Profesional destacado (>= 25 jobs, >= 10 reviews with avg >= 4.7, KYC verified, not suspended)
        $topProLevel = null;
        if ($completedJobsCount >= 25 && $validReviewsCount >= 10 && $avgRating >= 4.7 && $isVerifiedKYC && ! $isSuspended && ! $hasActiveSevereAction) {
            $topProLevel = AchievementLevel::DIAMOND;
        }
        $results['top_pro'] = $this->syncUserAchievement($user, 'top_pro', $topProLevel, [
            'completed_jobs' => $completedJobsCount,
            'avg_rating' => round($avgRating, 2),
            'kyc_verified' => $isVerifiedKYC,
        ]);

        return $results;
    }

    public function recalculateForClient(User $user): array
    {
        $this->seedStandardAchievements();

        $completedJobsCount = JobRequest::query()
            ->where('client_id', $user->id)
            ->whereHas('professional', fn ($q) => $q->where('user_id', '!=', $user->id))
            ->where('status', JobStatus::COMPLETED->value)
            ->count();

        $isSuspended = $user->isSuspended() || $user->isBanned();

        $clientLevel = null;
        if (! $isSuspended) {
            if ($completedJobsCount >= 50) {
                $clientLevel = AchievementLevel::DIAMOND;
            } elseif ($completedJobsCount >= 25) {
                $clientLevel = AchievementLevel::GOLD;
            } elseif ($completedJobsCount >= 10) {
                $clientLevel = AchievementLevel::SILVER;
            } elseif ($completedJobsCount >= 5) {
                $clientLevel = AchievementLevel::BRONZE;
            }
        }

        return [
            'good_client' => $this->syncUserAchievement($user, 'good_client', $clientLevel, [
                'completed_jobs' => $completedJobsCount,
            ]),
        ];
    }

    private function syncUserAchievement(User $user, string $code, ?AchievementLevel $level, array $metadata = []): ?UserAchievement
    {
        $achievement = Achievement::where('code', $code)->first();
        if (! $achievement) {
            return null;
        }

        $existing = UserAchievement::where('user_id', $user->id)
            ->where('achievement_id', $achievement->id)
            ->first();

        if ($level === null) {
            if ($existing && $existing->revoked_at === null) {
                $existing->update([
                    'revoked_at' => now(),
                    'revocation_reason' => 'Condiciones de mérito ya no se cumplen.',
                ]);
            }

            return null;
        }

        if ($existing) {
            $existing->update([
                'level' => $level,
                'revoked_at' => null,
                'revocation_reason' => null,
                'metadata' => $metadata,
            ]);

            return $existing->fresh(['achievement']);
        }

        return UserAchievement::create([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'level' => $level,
            'earned_at' => now(),
            'metadata' => $metadata,
        ])->load('achievement');
    }

    public function getPublicAchievementsForProfessional(ProfessionalProfile $profile): Collection
    {
        $this->recalculateForProfessional($profile);

        return UserAchievement::query()
            ->with('achievement')
            ->where('user_id', $profile->user_id)
            ->whereNull('revoked_at')
            ->whereHas('achievement', fn ($q) => $q->where('is_public', true)->where('is_active', true))
            ->get()
            ->map(fn (UserAchievement $ua) => [
                'code' => $ua->achievement->code,
                'name' => $ua->achievement->name,
                'level' => $ua->level->value,
                'level_label' => $ua->level->label(),
                'icon' => $ua->achievement->icon,
                'description' => $ua->achievement->description,
                'earned_at' => $ua->earned_at->toIso8601String(),
            ]);
    }

    public function getPublicAchievementsForUser(User $user): Collection
    {
        if ($user->canActAsProfessional() && $user->professionalProfile) {
            $this->recalculateForProfessional($user->professionalProfile);
        } else {
            $this->recalculateForClient($user);
        }

        return UserAchievement::query()
            ->with('achievement')
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->whereHas('achievement', fn ($q) => $q->where('is_public', true)->where('is_active', true))
            ->get()
            ->map(fn (UserAchievement $ua) => [
                'code' => $ua->achievement->code,
                'name' => $ua->achievement->name,
                'level' => $ua->level->value,
                'level_label' => $ua->level->label(),
                'icon' => $ua->achievement->icon,
                'description' => $ua->achievement->description,
                'earned_at' => $ua->earned_at->toIso8601String(),
            ]);
    }
}
