<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function __invoke()
    {
        try {
            return view('welcome', [
                'categories' => Category::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->limit(6)
                    ->get(),
                'professionals' => ProfessionalProfile::query()
                    ->with('user')
                    ->where('verification_status', VerificationStatus::VERIFIED)
                    ->whereHas('user', fn ($query) => $query
                        ->where('status', UserStatus::ACTIVE)
                        ->where('role', UserRole::PROFESSIONAL))
                    ->latest()
                    ->limit(3)
                    ->get(),
                'services' => Service::query()
                    ->with(['category', 'professional.user', 'coverImage'])
                    ->where('is_active', true)
                    ->whereHas('category', fn ($query) => $query->where('is_active', true))
                    ->whereHas('professional', fn ($query) => $query->where('verification_status', VerificationStatus::VERIFIED))
                    ->whereHas('professional.user', fn ($query) => $query
                        ->where('status', UserStatus::ACTIVE)
                        ->where('role', UserRole::PROFESSIONAL))
                    ->latest()
                    ->limit(3)
                    ->get(),
            ]);
        } catch (\Throwable) {
            return view('welcome', [
                'categories' => new Collection,
                'professionals' => new Collection,
                'services' => new Collection,
            ]);
        }
    }
}
