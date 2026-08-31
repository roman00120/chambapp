<?php

namespace App\Http\Controllers;

use App\Exceptions\CommerceFulfillmentException;
use App\Models\CommerceOrder;
use App\Models\Payment;
use App\Models\ProfessionalProfile;
use App\Services\CommerceService;
use App\Services\MercadoPagoService;
use App\Services\MercadoPagoWebhookSignature;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MercadoPagoWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        MercadoPagoWebhookSignature $signature,
        MercadoPagoService $mercadoPago,
        PaymentService $payments,
        CommerceService $commerce,
    ): JsonResponse {
        if (! $signature->isValid($request, config('services.mercadopago.webhook_secret'))) {
            Log::warning('Mercado Pago webhook signature rejected', [
                'event_id' => (string) $request->input('id'),
                'data_id' => (string) ($request->query('data.id') ?: data_get($request->input('data'), 'id')),
            ]);

            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $notificationType = strtolower((string) ($request->input('type') ?: $request->query('type') ?: $request->query('topic')));
        if ($notificationType !== '' && $notificationType !== 'payment') {
            return response()->json(['received' => true]);
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
        } catch (Throwable $exception) {
            Log::warning('Mercado Pago could not be queried for webhook processing', [
                'provider_payment_id' => $providerPaymentId,
                'exception' => $exception::class,
            ]);

            return response()->json(['message' => 'Provider unavailable.'], 503);
        }

        if (! hash_equals($providerPaymentId, (string) data_get($providerData, 'id'))) {
            Log::warning('Mercado Pago webhook provider payment id mismatch', [
                'provider_payment_id' => $providerPaymentId,
            ]);

            return response()->json(['received' => true]);
        }

        $reference = (string) data_get($providerData, 'external_reference');
        $safePayload = [
            'type' => (string) $request->input('type'),
            'action' => (string) $request->input('action'),
            'event_id' => (string) $request->input('id'),
            'provider_payment_id' => $providerPaymentId,
            'provider_status' => (string) data_get($providerData, 'status'),
            'provider_reference_present' => $reference !== '',
        ];
        $commerceOrder = CommerceOrder::query()->with(['professional', 'service'])->where('external_reference', $reference)->first();
        if ($commerceOrder) {
            try {
                $commerce->applyProviderPayment(
                    $commerceOrder,
                    $providerData,
                    (string) $request->input('id', $providerPaymentId),
                    $safePayload,
                );
            } catch (CommerceFulfillmentException $exception) {
                Log::error('Mercado Pago commerce fulfillment will be retried', [
                    'commerce_order_id' => $commerceOrder->getKey(),
                    'provider_payment_id' => $providerPaymentId,
                    'exception' => $exception::class,
                ]);

                return response()->json(['message' => 'Fulfillment pending retry.'], 503);
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

        $payments->applyProviderPayment($payment, $providerData, (string) $request->input('id', $providerPaymentId), $safePayload);

        if (config('queue.default') === 'database') {
            try {
                \Illuminate\Support\Facades\Artisan::call('queue:work', [
                    '--stop-when-empty' => true,
                    '--max-time' => 10,
                    '--tries' => 3,
                ]);
            } catch (Throwable) {}
        }

        return response()->json(['received' => true]);
    }
}
