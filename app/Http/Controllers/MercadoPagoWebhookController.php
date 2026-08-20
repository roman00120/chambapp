<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\CommerceOrder;
use App\Models\ProfessionalProfile;
use App\Services\MercadoPagoService;
use App\Services\MercadoPagoWebhookSignature;
use App\Services\PaymentService;
use App\Services\CommerceService;
use App\Services\PaymentCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        MercadoPagoWebhookSignature $signature,
        MercadoPagoService $mercadoPago,
        PaymentService $payments,
        CommerceService $commerce,
        PaymentCalculationService $calculation,
    ): JsonResponse {
        if (! $signature->isValid($request, config('services.mercadopago.webhook_secret'))) {
            Log::warning('Mercado Pago webhook signature rejected', [
                'event_id' => (string) $request->input('id'),
                'data_id' => (string) ($request->query('data.id') ?: data_get($request->input('data'), 'id')),
            ]);

            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $providerPaymentId = (string) ($request->query('data.id') ?: data_get($request->input('data'), 'id'));
        $sellerId = (string) $request->input('user_id');
        $professional = ProfessionalProfile::query()->where('mercadopago_user_id', $sellerId)->first();
        if ($providerPaymentId === '') {
            return response()->json(['received' => true]);
        }

        try {
            $providerData = $professional
                ? $mercadoPago->getPayment($providerPaymentId, $professional)
                : $mercadoPago->getPlatformPayment($providerPaymentId);
        } catch (\Throwable) {
            return response()->json(['message' => 'Provider unavailable.'], 202);
        }

        $reference = (string) data_get($providerData, 'external_reference');
        $commerceOrder = CommerceOrder::query()->with(['professional', 'service'])->where('external_reference', $reference)->first();
        if ($commerceOrder) {
            if (! $calculation->sameAmount((string) data_get($providerData, 'transaction_amount'), (string) $commerceOrder->amount)) {
                return response()->json(['received' => true]);
            }
            if (strtolower((string) data_get($providerData, 'status')) === 'approved') {
                $commerce->applyPaidOrder($commerceOrder);
            }
            return response()->json(['received' => true]);
        }
        if (! $professional) {
            return response()->json(['received' => true]);
        }
        $payment = Payment::query()->where('external_reference', $reference)->first();
        if (! $payment) {
            Log::warning('Mercado Pago webhook payment not found', [
                'provider_payment_id' => $providerPaymentId,
                'external_reference_present' => $reference !== '',
            ]);

            return response()->json(['received' => true]);
        }
        if ($payment->professional_id !== $professional->getKey()
            || (filled(data_get($providerData, 'collector_id')) && (string) data_get($providerData, 'collector_id') !== $sellerId)) {
            Log::warning('Mercado Pago webhook seller mismatch', [
                'provider_payment_id' => $providerPaymentId,
                'external_reference_present' => true,
            ]);

            return response()->json(['received' => true]);
        }

        $safePayload = [
            'type' => (string) $request->input('type'),
            'action' => (string) $request->input('action'),
            'event_id' => (string) $request->input('id'),
            'provider_payment_id' => $providerPaymentId,
            'provider_status' => (string) data_get($providerData, 'status'),
            'provider_reference_present' => $reference !== '',
        ];
        $payments->applyProviderPayment($payment, $providerData, (string) $request->input('id', $providerPaymentId), $safePayload);

        return response()->json(['received' => true]);
    }
}
