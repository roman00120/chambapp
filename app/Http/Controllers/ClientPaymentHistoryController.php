<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientPaymentHistoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('payments.client-index', [
            'payments' => $request->user()->payments()->with(['jobRequest.service', 'professional.user'])->latest()->paginate(12),
        ]);
    }
}
