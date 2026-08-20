<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\JobDispute;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use App\Models\Report;
use App\Models\Service;
use App\Models\User;
use App\Services\AdminDateRangeService;
use App\Services\PaymentCalculationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, AdminDateRangeService $ranges, PaymentCalculationService $money): View
    {
        [$from, $to, $range] = $ranges->resolve($request);
        $created = fn ($query) => $query->whereBetween('created_at', [$from, $to]);
        $paid = fn ($query) => $query->where('status', PaymentStatus::APPROVED->value)->whereBetween('paid_at', [$from, $to]);
        $gross = Payment::query()->tap($paid)->sum('gross_amount');
        $fees = Payment::query()->tap($paid)->sum('platform_fee');

        $stats = [
            'users' => User::count(),
            'clients' => User::where('role', UserRole::CLIENT->value)->count(),
            'professionals' => User::where('role', UserRole::PROFESSIONAL->value)->count(),
            'verifiedProfessionals' => ProfessionalProfile::where('verification_status', VerificationStatus::VERIFIED->value)->count(),
            'activeServices' => Service::active()->count(),
            'pendingJobs' => JobRequest::query()->tap($created)->where('status', JobStatus::PENDING->value)->count(),
            'inProgressJobs' => JobRequest::query()->tap($created)->where('status', JobStatus::IN_PROGRESS->value)->count(),
            'completedJobs' => JobRequest::query()->tap($created)->where('status', JobStatus::COMPLETED->value)->count(),
            'approvedPayments' => Payment::query()->tap($paid)->count(),
            'gross' => $gross,
            'fees' => $fees,
            'grossFormatted' => $money->formatAmount((string) $gross),
            'feesFormatted' => $money->formatAmount((string) $fees),
            'openDisputes' => JobDispute::query()->tap($created)->whereIn('status', ['open', 'reviewing'])->count(),
            'pendingReports' => Report::query()->tap($created)->where('status', 'pending')->count(),
            'pendingVerifications' => ProfessionalProfile::where('verification_status', VerificationStatus::PENDING->value)->count(),
            'categories' => Category::count(),
        ];

        return view('admin.dashboard', compact('stats', 'range', 'from', 'to'));
    }
}
