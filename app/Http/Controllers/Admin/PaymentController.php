<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = Payment::query()
            ->with(['client', 'professional.user', 'jobRequest'])
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(function ($search) use ($term): void {
                $search->where('external_reference', 'like', '%'.$term.'%')->orWhere('external_payment_id', 'like', '%'.$term.'%');
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function show(Payment $payment): View
    {
        $payment->load(['client', 'professional.user', 'jobRequest.service', 'jobRequest.quotes' => fn ($query) => $query->where('status', 'accepted'), 'transactions']);

        return view('admin.payments.show', compact('payment'));
    }
}
