<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\Service;
use App\Services\ProfessionalIdentityVerificationService;
use Illuminate\View\View;

class PublicServiceController extends Controller
{
    public function show(Service $service, ProfessionalIdentityVerificationService $identityVerification): View
    {
        $service = Service::query()
            ->active()
            ->whereKey($service->getKey())
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->whereHas('professional', function ($profile) use ($identityVerification): void {
                $profile->publiclyVisible();
                $identityVerification->applyOperationalEligibility($profile);
            })
            ->with([
                'category:id,name,slug,description',
                'professional:id,user_id,bio,city,state,verification_status,profile_photo,average_rating,total_reviews,total_completed_jobs',
                'professional.user:id,name,email,status,role',
                'professional.identityVerification:id,professional_id,status,expires_at',
                'images:id,service_id,path,alt_text,sort_order,is_cover',
            ])
            ->firstOrFail();

        abort_unless($identityVerification->professionalCanAcceptJobs($service->professional), 404);

        return view('marketplace.service-detail', [
            'service' => $service,
            'isFavorite' => auth()->check() && auth()->user()->favorites()->where('professional_id', $service->professional_id)->exists(),
        ]);
    }
}
