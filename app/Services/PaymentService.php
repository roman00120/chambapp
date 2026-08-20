<?php

namespace App\Services;

use App\Enums\JobStatus;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Exceptions\MercadoPagoException;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\ChambappNotification;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaymentService
{
    public function __construct(
        private readonly PaymentCalculationService $calculation,
        private readonly MercadoPagoService $mercadoPago,
        private readonly PaymentStatusMapper $statusMapper,
    ) {}

    public function startCheckout(JobRequest $jobRequest, User $client): Payment
    {
        $payment = $this->createOrReusePendingPayment($jobRequest, $client);
        if (filled($payment->checkout_url)) {
            return $payment;
        }

        try {
            $preference = $this->mercadoPago->createPreference($payment);
            $payment->forceFill([
                'external_preference_id' => $preference['id'],
                'checkout_url' => $preference['url'],
            ])->save();
            $payment->transactions()->create([
                'event_type' => 'checkout.created',
                'payload' => [
                    'preference_id' => $preference['id'],
                    'external_reference' => $payment->external_reference,
                ],
            ]);
        } catch (MercadoPagoException $exception) {
            $payment->transactions()->create([
                'event_type' => 'checkout.failed',
                'payload' => ['reason' => 'provider_unavailable'],
            ]);

            throw $exception;
        } catch (Throwable $exception) {
            $payment->transactions()->create([
                'event_type' => 'checkout.failed',
                'payload' => ['reason' => 'unexpected_provider_error'],
            ]);

            throw new MercadoPagoException('No pudimos iniciar el pago en este momento. Intenta nuevamente.', 0, $exception);
        }

        return $payment->fresh();
    }

    public function applyProviderPayment(Payment $payment, array $providerData, ?string $providerEventId, array $auditPayload): Payment
    {
        return DB::transaction(function () use ($payment, $providerData, $providerEventId, $auditPayload): Payment {
            $lockedPayment = Payment::query()->with(['jobRequest', 'client', 'professional.user'])->lockForUpdate()->findOrFail($payment->getKey());
            if ($providerEventId && $lockedPayment->transactions()->where('provider_event_id', $providerEventId)->exists()) {
                return $lockedPayment->fresh();
            }

            $lockedPayment->transactions()->create([
                'event_type' => 'webhook.received',
                'provider_event_id' => $providerEventId,
                'payload' => $auditPayload,
            ]);

            $referenceMatches = hash_equals((string) $lockedPayment->external_reference, (string) data_get($providerData, 'external_reference', ''));
            $amountMatches = filled(data_get($providerData, 'transaction_amount'))
                && $this->calculation->sameAmount((string) data_get($providerData, 'transaction_amount'), (string) $lockedPayment->gross_amount);
            $currencyMatches = strtoupper((string) data_get($providerData, 'currency_id')) === strtoupper((string) $lockedPayment->currency);

            if (! $referenceMatches || ! $amountMatches || ! $currencyMatches) {
                $lockedPayment->transactions()->create([
                    'event_type' => 'webhook.rejected',
                    'payload' => [
                        'reason' => ! $referenceMatches ? 'reference_mismatch' : (! $amountMatches ? 'amount_mismatch' : 'currency_mismatch'),
                        'provider_payment_id' => (string) data_get($providerData, 'id'),
                        'provider_amount' => (string) data_get($providerData, 'transaction_amount'),
                        'provider_currency' => (string) data_get($providerData, 'currency_id'),
                    ],
                ]);

                return $lockedPayment->fresh();
            }

            $mappedStatus = $this->statusMapper->map(data_get($providerData, 'status'));
            $previousStatus = $lockedPayment->status;
            $attributes = [
                'external_payment_id' => (string) data_get($providerData, 'id'),
                'status' => $mappedStatus,
            ];
            $providerFee = data_get($providerData, 'fee_details.0.amount');
            if (filled($providerFee)) {
                $attributes['provider_fee'] = $this->calculation->normalize((string) $providerFee);
            }
            if ($mappedStatus === PaymentStatus::APPROVED) {
                $attributes['paid_at'] = $lockedPayment->paid_at ?? now();
            }
            if (in_array($mappedStatus, [PaymentStatus::REFUNDED, PaymentStatus::PARTIALLY_REFUNDED], true)) {
                $attributes['refunded_at'] = $lockedPayment->refunded_at ?? now();
            }

            $lockedPayment->forceFill($attributes)->save();
            if ($previousStatus !== $mappedStatus) {
                $lockedPayment->transactions()->create([
                    'event_type' => 'payment.status_changed',
                    'payload' => [
                        'from' => $previousStatus?->value,
                        'to' => $mappedStatus->value,
                        'provider_payment_id' => (string) data_get($providerData, 'id'),
                    ],
                ]);
            }

            if ($mappedStatus === PaymentStatus::APPROVED && $lockedPayment->jobRequest->status === JobStatus::AWAITING_PAYMENT) {
                $lockedPayment->jobRequest->forceFill(['status' => JobStatus::PAID])->save();
                $lockedPayment->client?->notify(new ChambappNotification(
                    'payment_approved',
                    'Tu pago fue aprobado',
                    'El trabajo ya está contratado dentro de Chambapp.',
                    route('job-requests.show', $lockedPayment->jobRequest),
                ));
                $lockedPayment->professional?->user?->notify(new ChambappNotification(
                    'payment_approved_professional',
                    'El cliente realizó el pago',
                    'Ya puedes coordinar e iniciar el trabajo.',
                    route('job-requests.show', $lockedPayment->jobRequest),
                ));
            } elseif ($mappedStatus === PaymentStatus::REJECTED && $previousStatus !== PaymentStatus::REJECTED) {
                $lockedPayment->client?->notify(new ChambappNotification(
                    'payment_rejected',
                    'El pago no pudo ser procesado',
                    'Puedes intentar nuevamente desde el resumen del pago.',
                    route('client.payments.summary', $lockedPayment->jobRequest),
                ));
            }

            return $lockedPayment->fresh();
        });
    }

    private function createOrReusePendingPayment(JobRequest $jobRequest, User $client): Payment
    {
        return DB::transaction(function () use ($jobRequest, $client): Payment {
            $job = JobRequest::query()->with(['professional', 'quotes'])->lockForUpdate()->findOrFail($jobRequest->getKey());
            if ($job->client_id !== $client->getKey()) {
                throw new DomainException('No puedes pagar este trabajo.');
            }
            if ($job->status !== JobStatus::AWAITING_PAYMENT) {
                throw new DomainException('Este trabajo no está listo para pago.');
            }
            if (! $job->quotes()->where('status', QuoteStatus::ACCEPTED->value)->exists()) {
                throw new DomainException('Debe existir una cotización aceptada.');
            }
            if (! $job->professional?->isMercadoPagoConnected()) {
                throw new DomainException('El profesional debe conectar Mercado Pago para recibir pagos.');
            }
            $approved = $job->payments()->where('status', PaymentStatus::APPROVED->value)->exists();
            if ($approved) {
                throw new DomainException('Este trabajo ya tiene un pago aprobado.');
            }

            $active = $job->payments()
                ->whereIn('status', [PaymentStatus::PENDING->value, PaymentStatus::PROCESSING->value])
                ->latest('id')
                ->first();
            if ($active) {
                return $active;
            }

            $money = $this->calculation->calculate((string) $job->agreed_price);
            $payment = $job->payments()->create([
                'client_id' => $job->client_id,
                'professional_id' => $job->professional_id,
                'provider' => config('chambapp.payments.provider'),
                'currency' => $money->currency,
                'gross_amount' => $money->grossAmount,
                'platform_fee_percent' => $money->platformFeePercent,
                'platform_fee' => $money->platformFee,
                'provider_fee' => null,
                'professional_amount' => $money->professionalAmount,
                'status' => PaymentStatus::PENDING,
            ]);
            $payment->forceFill([
                'external_reference' => sprintf('CHAMBAPP-JOB-%06d-PAY-%06d', $job->getKey(), $payment->getKey()),
            ])->save();
            $payment->transactions()->create([
                'event_type' => 'payment.created',
                'payload' => [
                    'currency' => $money->currency,
                    'gross_amount' => $money->grossAmount,
                    'platform_fee_percent' => $money->platformFeePercent,
                    'platform_fee' => $money->platformFee,
                    'professional_amount' => $money->professionalAmount,
                ],
            ]);

            return $payment;
        });
    }
}
