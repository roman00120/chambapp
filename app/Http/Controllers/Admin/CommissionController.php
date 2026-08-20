<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\AdminDateRangeService;
use App\Services\PaymentCalculationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function index(Request $request, AdminDateRangeService $ranges, PaymentCalculationService $money): View
    {
        [$from, $to, $range] = $ranges->resolve($request);
        $query = Payment::query()
            ->where('status', PaymentStatus::APPROVED->value)
            ->whereBetween('paid_at', [$from, $to]);
        $payments = (clone $query)->with(['professional.user', 'jobRequest'])->latest('paid_at')->paginate(20)->withQueryString();

        return view('admin.commissions.index', [
            'payments' => $payments,
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'gross' => $money->formatAmount((string) $query->sum('gross_amount')),
            'fees' => $money->formatAmount((string) $query->sum('platform_fee')),
            'professionalAmount' => $money->formatAmount((string) $query->sum('professional_amount')),
            'currentPercent' => config('chambapp.payments.platform_fee_percent', '15'),
        ]);
    }
}
