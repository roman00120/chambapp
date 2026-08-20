<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function show(Request $request, Payment $payment): PaymentResource
    {
        $payment->load('professional.user');
        abort_unless(
            $payment->client_id === $request->user()->id
                || $payment->professional?->user_id === $request->user()->id,
            403,
        );

        return new PaymentResource($payment);
    }
}
