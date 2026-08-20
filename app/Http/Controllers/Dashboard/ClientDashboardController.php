<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\JobStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $services = Service::query()
            ->with(['category', 'professional.user', 'coverImage'])
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->whereHas('professional', fn ($query) => $query->where('verification_status', VerificationStatus::VERIFIED))
            ->whereHas('professional.user', fn ($query) => $query
                ->where('status', UserStatus::ACTIVE)
                ->where('role', UserRole::PROFESSIONAL))
            ->latest()
            ->limit(3)
            ->get();

        return view('dashboards.client', [
            'user' => $request->user(),
            'categories' => Category::active()->orderBy('sort_order')->limit(6)->get(),
            'services' => $services,
            'favoriteProfessionalIds' => $request->user()->favorites()->pluck('professional_id')->all(),
            'pendingJobsCount' => $request->user()->jobRequests()->where('status', JobStatus::PENDING)->count(),
            'activeJobsCount' => $request->user()->jobRequests()->whereIn('status', [JobStatus::SEARCHING, JobStatus::MATCHED, JobStatus::AWAITING_QUOTE, JobStatus::ACCEPTED, JobStatus::AWAITING_PAYMENT, JobStatus::PAID, JobStatus::ON_THE_WAY, JobStatus::ARRIVED, JobStatus::IN_PROGRESS, JobStatus::AWAITING_CONFIRMATION])->count(),
            'recentJobs' => $request->user()->jobRequests()->with(['service', 'professional.user', 'review'])->latest()->limit(3)->get(),
            'pendingReviewsCount' => $request->user()->jobRequests()->where('status', JobStatus::COMPLETED)->whereDoesntHave('review')->count(),
        ]);
    }
}
