<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\JobStatus;
use App\Enums\PaymentKind;
use App\Enums\PaymentStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\ProfessionalIdentityVerificationService;

class ProfessionalDashboardController extends Controller
{
    public function __invoke(Request $request, ProfessionalIdentityVerificationService $identityVerification): View
    {
        $user = $request->user();
        $profile = $user->professionalProfile()->firstOrCreate([
            'user_id' => $user->getKey(),
        ], [
            'verification_status' => VerificationStatus::UNVERIFIED,
        ]);

        $approvedPaymentTotals = $profile->payments()
            ->where('status', PaymentStatus::APPROVED->value)
            ->selectRaw('COALESCE(SUM(COALESCE(base_amount, gross_amount)), 0) as base_revenue')
            ->selectRaw('COALESCE(SUM(COALESCE(professional_commission, platform_fee)), 0) as professional_commissions')
            ->selectRaw('COALESCE(SUM(COALESCE(professional_amount_before_external_costs, professional_amount)), 0) as professional_revenue_before_external_costs')
            ->first();

        $identityRecord = $identityVerification->recordFor($profile);
        $profile->setRelation('identityVerification', $identityRecord);

        return view('dashboards.professional', [
            'user' => $user,
            'profile' => $profile->load('user'),
            'identityStatus' => $identityVerification->statusFor($profile),
            'identityRequired' => $identityVerification->isRequired(),
            'canAcceptJobs' => $identityVerification->professionalCanAcceptJobs($profile),
            'activeServicesCount' => $profile->services()->active()->count(),
            'totalServicesCount' => $profile->services()->count(),
            'pendingRequestsCount' => $profile->jobRequests()->where('status', JobStatus::PENDING)->count(),
            'activeJobsCount' => $profile->jobRequests()->whereIn('status', [JobStatus::MATCHED, JobStatus::AWAITING_QUOTE, JobStatus::ACCEPTED, JobStatus::AWAITING_PAYMENT, JobStatus::PAID, JobStatus::ON_THE_WAY, JobStatus::ARRIVED, JobStatus::IN_PROGRESS, JobStatus::AWAITING_CONFIRMATION])->count(),
            'completedJobsCount' => $profile->jobRequests()->where('status', JobStatus::COMPLETED)->count(),
            'rating' => $profile->total_reviews > 0 ? number_format((float) $profile->average_rating, 1) : 'Nuevo',
            'totalReviews' => $profile->total_reviews,
            'paidJobsCount' => $profile->payments()
                ->where('kind', PaymentKind::JOB->value)
                ->where('status', PaymentStatus::APPROVED->value)
                ->distinct()
                ->count('job_request_id'),
            'baseRevenue' => $approvedPaymentTotals->base_revenue,
            'professionalCommissions' => $approvedPaymentTotals->professional_commissions,
            'professionalRevenueBeforeExternalCosts' => $approvedPaymentTotals->professional_revenue_before_external_costs,
        ]);
    }
}
