<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\Service;
use Illuminate\View\View;

class PublicServiceController extends Controller
{
    public function show(Service $service): View
    {
        $service = Service::query()
            ->active()
            ->whereKey($service->getKey())
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->whereHas('professional', fn ($profile) => $profile->where('verification_status', VerificationStatus::VERIFIED->value))
            ->whereHas('professional.user', fn ($query) => $query->where('status', UserStatus::ACTIVE->value)->where('role', UserRole::PROFESSIONAL->value))
            ->with([
                'category:id,name,slug,description',
                'professional:id,user_id,bio,city,state,verification_status,profile_photo,average_rating,total_reviews,total_completed_jobs',
                'professional.user:id,name,status,role',
                'images:id,service_id,path,alt_text,sort_order,is_cover',
            ])
            ->firstOrFail();

        return view('marketplace.service-detail', [
            'service' => $service,
            'isFavorite' => auth()->check() && auth()->user()->favorites()->where('professional_id', $service->professional_id)->exists(),
        ]);
    }
}
