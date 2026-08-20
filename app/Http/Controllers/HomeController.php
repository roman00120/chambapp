<?php

namespace App\Http\Controllers;

use App\Enums\AvailabilityStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\ProfessionalProfile;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function __invoke()
    {
        try {
            $verifiedProfessionals = ProfessionalProfile::query()
                ->where('verification_status', VerificationStatus::VERIFIED)
                ->whereHas('user', fn ($query) => $query
                    ->where('status', UserStatus::ACTIVE)
                    ->where('role', UserRole::PROFESSIONAL));

            $activeServices = Service::query()
                ->where('is_active', true)
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->whereHas('professional', fn ($query) => $query->where('verification_status', VerificationStatus::VERIFIED))
                ->whereHas('professional.user', fn ($query) => $query
                    ->where('status', UserStatus::ACTIVE)
                    ->where('role', UserRole::PROFESSIONAL));

            $visibleReviews = Review::query()->visible();
            $totalReviews = (clone $visibleReviews)->count();

            return view('welcome', [
                'categories' => Category::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->limit(6)
                    ->get(),
                'professionals' => (clone $verifiedProfessionals)
                    ->with('user')
                    ->latest()
                    ->limit(3)
                    ->get(),
                'services' => (clone $activeServices)
                    ->with(['category', 'professional.user', 'coverImage'])
                    ->latest()
                    ->limit(3)
                    ->get(),
                'homeStats' => [
                    'verified_professionals' => (clone $verifiedProfessionals)->count(),
                    'completed_jobs' => (int) (clone $verifiedProfessionals)->sum('total_completed_jobs'),
                    'average_rating' => $totalReviews > 0 ? (float) (clone $visibleReviews)->avg('rating') : null,
                    'total_reviews' => $totalReviews,
                    'active_services' => (clone $activeServices)->count(),
                    'available_professionals' => (clone $verifiedProfessionals)
                        ->where('is_available', true)
                        ->where('availability_status', AvailabilityStatus::AVAILABLE)
                        ->where('location_updated_at', '>=', now()->subMinutes((int) config('chambapp.on_demand.location_freshness_minutes', 30)))
                        ->count(),
                ],
            ]);
        } catch (\Throwable) {
            return view('welcome', [
                'categories' => new Collection,
                'professionals' => new Collection,
                'services' => new Collection,
                'homeStats' => [
                    'verified_professionals' => 0,
                    'completed_jobs' => 0,
                    'average_rating' => null,
                    'total_reviews' => 0,
                    'active_services' => 0,
                    'available_professionals' => 0,
                ],
            ]);
        }
    }
}
