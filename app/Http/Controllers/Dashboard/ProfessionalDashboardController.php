<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\JobStatus;
use App\Enums\PaymentStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $profile = $user->professionalProfile()->firstOrCreate([
            'user_id' => $user->getKey(),
        ], [
            'verification_status' => VerificationStatus::UNVERIFIED,
        ]);

        return view('dashboards.professional', [
            'user' => $user,
            'profile' => $profile->load('user'),
            'activeServicesCount' => $profile->services()->active()->count(),
            'totalServicesCount' => $profile->services()->count(),
            'pendingRequestsCount' => $profile->jobRequests()->where('status', JobStatus::PENDING)->count(),
            'activeJobsCount' => $profile->jobRequests()->whereIn('status', [JobStatus::MATCHED, JobStatus::AWAITING_QUOTE, JobStatus::ACCEPTED, JobStatus::AWAITING_PAYMENT, JobStatus::PAID, JobStatus::ON_THE_WAY, JobStatus::ARRIVED, JobStatus::IN_PROGRESS, JobStatus::AWAITING_CONFIRMATION])->count(),
            'completedJobsCount' => $profile->jobRequests()->where('status', JobStatus::COMPLETED)->count(),
            'rating' => $profile->total_reviews > 0 ? number_format((float) $profile->average_rating, 1) : 'Nuevo',
            'totalReviews' => $profile->total_reviews,
            'paidJobsCount' => $profile->payments()->where('status', PaymentStatus::APPROVED->value)->count(),
            'grossRevenue' => $profile->payments()->where('status', PaymentStatus::APPROVED->value)->sum('gross_amount'),
            'platformFees' => $profile->payments()->where('status', PaymentStatus::APPROVED->value)->sum('platform_fee'),
            'professionalRevenue' => $profile->payments()->where('status', PaymentStatus::APPROVED->value)->sum('professional_amount'),
        ]);
    }
}
