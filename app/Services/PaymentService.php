<?php

namespace App\Services;

use App\Enums\JobStatus;
use App\Enums\PaymentKind;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Exceptions\MercadoPagoException;
use App\Models\JobRequest;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\ChambappNotification;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentService
{
    public function __construct(
        private readonly PaymentCalculationService $calculation,
        private readonly MercadoPagoService $mercadoPago,
        private readonly PaymentStatusMapper $statusMapper,
        private readonly ProfessionalIdentityVerificationService $identityVerification,
    ) {}

    public function startCheckout(JobRequest $jobRequest, User $client): Payment
    {
        return $this->createOrReuseCheckoutPreference(
            $this->createOrReuseJobPayment($jobRequest, $client),
        );
    }

    public function startTipCheckout(JobRequest $jobRequest, User $client, string $amount): Payment
    {
        $payment = DB::transaction(function () use ($jobRequest, $client, $amount): Payment {
            $job = JobRequest::query()->with('professional')->lockForUpdate()->findOrFail($jobRequest->getKey());
            if ($job->client_id !== $client->getKey() || $job->status !== JobStatus::COMPLETED) {
                throw new DomainException('La propina solo puede agregarse a un trabajo completado.');
            }
            if (! $job->professional?->isMercadoPagoConnected()) {
                throw new DomainException('El profesional debe conectar Mercado Pago para recibir la propina.');
            }
            $money = $this->calculation->calculate($amount);
            $active = $job->payments()
                ->where('kind', PaymentKind::TIP->value)
                ->where('gross_amount', $money->grossAmount)
                ->whereIn('status', [
                    PaymentStatus::PENDING->value,
                    PaymentStatus::PROCESSING->value,
                    PaymentStatus::REJECTED->value,
                    PaymentStatus::CANCELLED->value,
                ])
                ->latest('id')
                ->first();
            if ($active) {
                return $active;
            }

            $payment = $job->payments()->create([
                'client_id' => $job->client_id,
                'professional_id' => $job->professional_id,
                'provider' => config('chambapp.payments.provider'),
                'kind' => PaymentKind::TIP,
                'currency' => $money->currency,
                'gross_amount' => $money->grossAmount,
                'platform_fee_percent' => $money->platformFeePercent,
                'platform_fee' => $money->platformFee,
                'provider_fee' => null,
                'professional_amount' => $money->professionalAmount,
                'tip_amount' => $money->grossAmount,
                'tip_platform_fee' => $money->platformFee,
                'tip_professional_amount' => $money->professionalAmount,
                'status' => PaymentStatus::PENDING,
            ]);
            $payment->forceFill(['external_reference' => sprintf('CHAMBAPP-JOB-%06d-TIP-%06d', $job->getKey(), $payment->getKey())])->save();
            $payment->transactions()->create([
                'event_type' => 'payment.created',
                'payload' => [
                    'kind' => PaymentKind::TIP->value,
                    'currency' => $money->currency,
                    'gross_amount' => $money->grossAmount,
                    'platform_fee_percent' => $money->platformFeePercent,
                    'platform_fee' => $money->platformFee,
                    'professional_amount' => $money->professionalAmount,
                ],
            ]);

            return $payment;
        });

        return $this->createOrReuseCheckoutPreference($payment);
    }

    public function applyProviderPayment(Payment $payment, array $providerData, ?string $providerEventId, array $auditPayload): Payment
    {
        return DB::transaction(function () use ($payment, $providerData, $providerEventId, $auditPayload): Payment {
            $job = JobRequest::query()->lockForUpdate()->findOrFail($payment->job_request_id);
            $lockedPayment = Payment::query()->with(['client', 'professional.user'])->lockForUpdate()->findOrFail($payment->getKey());
            $lockedPayment->setRelation('jobRequest', $job);
            $providerPaymentId = (string) data_get($providerData, 'id');
            $providerEventId = trim((string) $providerEventId);
            if ($providerEventId === '' && $providerPaymentId !== '') {
                $providerEventId = 'provider-'.hash('sha256', implode('|', [
                    (string) $lockedPayment->getKey(),
                    $providerPaymentId,
                    (string) data_get($providerData, 'status'),
                    (string) data_get($providerData, 'date_last_updated'),
                ]));
            }
            $providerEventId = $providerEventId !== '' ? $providerEventId : null;
            if ($providerEventId && $lockedPayment->transactions()->where('provider_event_id', $providerEventId)->exists()) {
                return $lockedPayment->fresh();
            }

            $lockedPayment->transactions()->create([
                'event_type' => 'webhook.received',
                'provider_event_id' => $providerEventId,
                'payload' => $auditPayload,
            ]);

            $providerLiveMode = filter_var(data_get($providerData, 'live_mode'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            $referenceMatches = hash_equals((string) $lockedPayment->external_reference, (string) data_get($providerData, 'external_reference', ''));
            $providerGrossAmount = $this->normalizeExternalAmount(data_get($providerData, 'transaction_amount'));
            $amountMatches = $providerGrossAmount !== null
                && $this->calculation->sameAmount($providerGrossAmount, (string) $lockedPayment->gross_amount);
            $currencyMatches = strtoupper((string) data_get($providerData, 'currency_id')) === strtoupper((string) $lockedPayment->currency);
            $modeMatches = $providerLiveMode !== null && $providerLiveMode === app()->environment('production');
            $collectorMatches = $lockedPayment->professional !== null
                && filled(data_get($providerData, 'collector_id'))
                && hash_equals((string) $lockedPayment->professional->mercadopago_user_id, (string) data_get($providerData, 'collector_id'));
            $mismatchReason = match (true) {
                $providerPaymentId === '' => 'provider_payment_id_missing',
                ! $referenceMatches => 'reference_mismatch',
                ! $amountMatches => 'amount_mismatch',
                ! $currencyMatches => 'currency_mismatch',
                ! $modeMatches => 'environment_mismatch',
                ! $collectorMatches => 'collector_mismatch',
                default => null,
            };

            if ($mismatchReason !== null) {
                $lockedPayment->transactions()->create([
                    'event_type' => 'webhook.rejected',
                    'payload' => [
                        'reason' => $mismatchReason,
                        'provider_payment_id' => $providerPaymentId,
                        'provider_amount' => (string) data_get($providerData, 'transaction_amount'),
                        'provider_currency' => (string) data_get($providerData, 'currency_id'),
                    ],
                ]);

                return $lockedPayment->fresh();
            }

            $auxiliaryIssues = [];
            $mappedStatus = $this->statusMapper->map(data_get($providerData, 'status'));
            $rawRefundedAmount = data_get($providerData, 'transaction_amount_refunded');
            $refundedAmount = $this->normalizeExternalAmount(
                $rawRefundedAmount,
                (string) $lockedPayment->gross_amount,
            );
            if (filled($rawRefundedAmount) && $refundedAmount === null) {
                $auxiliaryIssues[] = 'refunded_amount_invalid';
            }
            if ($mappedStatus !== PaymentStatus::CHARGED_BACK
                && $refundedAmount !== null
                && ! $this->calculation->sameAmount($refundedAmount, '0')) {
                $mappedStatus = $this->calculation->sameAmount($refundedAmount, (string) $lockedPayment->gross_amount)
                    ? PaymentStatus::REFUNDED
                    : PaymentStatus::PARTIALLY_REFUNDED;
            }
            $previousStatus = $lockedPayment->status;
            if (filled($lockedPayment->external_payment_id)
                && ! hash_equals((string) $lockedPayment->external_payment_id, $providerPaymentId)
                && $this->hasBoundFinancialAttempt($previousStatus)) {
                $eventType = $mappedStatus === PaymentStatus::APPROVED
                    ? 'payment.duplicate_approved_detected'
                    : 'webhook.ignored';
                $duplicateAlreadyReported = $eventType === 'payment.duplicate_approved_detected'
                    && $lockedPayment->transactions()->where('event_type', $eventType)->exists();
                $lockedPayment->transactions()->create([
                    'event_type' => $eventType,
                    'payload' => [
                        'reason' => 'different_provider_payment_after_financial_state',
                        'bound_provider_payment_id' => (string) $lockedPayment->external_payment_id,
                        'incoming_provider_payment_id' => $providerPaymentId,
                        'incoming_status' => $mappedStatus->value,
                    ],
                ]);
                if ($mappedStatus === PaymentStatus::APPROVED) {
                    Log::critical('Duplicate Mercado Pago approval detected for one Chambapp payment', [
                        'payment_id' => $lockedPayment->getKey(),
                        'job_request_id' => $job->getKey(),
                    ]);
                    $this->markDuplicateApprovalIncident($lockedPayment, $job, ! $duplicateAlreadyReported);
                }

                return $lockedPayment->fresh();
            }
            if ($this->isTerminalRegression($previousStatus, $mappedStatus)) {
                $lockedPayment->transactions()->create([
                    'event_type' => 'webhook.ignored',
                    'payload' => [
                        'reason' => 'financial_status_regression',
                        'from' => $previousStatus->value,
                        'to' => $mappedStatus->value,
                        'provider_payment_id' => $providerPaymentId,
                    ],
                ]);

                return $lockedPayment->fresh();
            }

            $attributes = [
                'external_payment_id' => $providerPaymentId,
                'status' => $mappedStatus,
            ];
            $providerFeeDetails = collect((array) data_get($providerData, 'fee_details', []));
            $rawProviderFees = $providerFeeDetails
                ->filter(fn ($fee): bool => strtolower((string) data_get($fee, 'type')) === 'mercadopago_fee')
                ->pluck('amount')
                ->filter(fn ($amount): bool => filled($amount));
            if ($rawProviderFees->isNotEmpty()) {
                $providerFees = $rawProviderFees
                    ->map(fn (mixed $amount): ?string => $this->normalizeExternalAmount($amount))
                    ->filter(fn (?string $amount): bool => $amount !== null);
                if ($providerFees->count() !== $rawProviderFees->count()) {
                    $auxiliaryIssues[] = 'provider_fee_invalid';
                } else {
                    $totalProviderFee = $this->calculation->sum($providerFees);
                    if ($this->calculation->isAtMost($totalProviderFee, (string) $lockedPayment->gross_amount)) {
                        $attributes['provider_fee'] = $totalProviderFee;
                    } else {
                        $auxiliaryIssues[] = 'provider_fee_exceeds_gross';
                    }
                }
            }
            $splitMismatch = false;
            $rawMarketplaceFees = $providerFeeDetails
                ->filter(fn ($fee): bool => in_array(strtolower((string) data_get($fee, 'type')), ['marketplace_fee', 'application_fee'], true))
                ->pluck('amount')
                ->filter(fn ($amount): bool => filled($amount));
            if ($rawMarketplaceFees->isNotEmpty()) {
                $marketplaceFees = $rawMarketplaceFees
                    ->map(fn (mixed $amount): ?string => $this->normalizeExternalAmount($amount))
                    ->filter(fn (?string $amount): bool => $amount !== null);
                $expectedMarketplaceFee = (string) ($lockedPayment->platform_gross_fee ?? $lockedPayment->platform_fee);
                if ($marketplaceFees->count() !== $rawMarketplaceFees->count()
                    || ! $this->calculation->sameAmount($this->calculation->sum($marketplaceFees), $expectedMarketplaceFee)) {
                    $splitMismatch = true;
                    $auxiliaryIssues[] = 'marketplace_fee_mismatch';
                }
            }
            $netReceived = data_get($providerData, 'transaction_details.net_received_amount');
            if (filled($netReceived)) {
                $normalizedNet = $this->normalizeExternalAmount($netReceived);
                $expectedMaximum = (string) ($lockedPayment->professional_amount_before_external_costs
                    ?? $this->calculation->calculate(
                        (string) $lockedPayment->gross_amount,
                        (string) $lockedPayment->platform_fee_percent,
                    )->professionalAmount);
                if ($normalizedNet === null) {
                    $auxiliaryIssues[] = 'net_received_amount_invalid';
                } elseif ($this->calculation->isAtMost($normalizedNet, $expectedMaximum)) {
                    $attributes['professional_amount'] = $normalizedNet;
                    if ($lockedPayment->kind === PaymentKind::TIP) {
                        $attributes['tip_professional_amount'] = $normalizedNet;
                    }
                } else {
                    $splitMismatch = true;
                    $auxiliaryIssues[] = 'net_received_amount_exceeds_expected_split';
                }
            }
            if ($mappedStatus === PaymentStatus::APPROVED
                || (($this->isPaymentRisk($mappedStatus)) && filled(data_get($providerData, 'date_approved')))) {
                $attributes['paid_at'] = $lockedPayment->paid_at ?? $this->providerDate(data_get($providerData, 'date_approved'));
            }
            if ($this->isFinancialLoss($mappedStatus)) {
                $attributes['refunded_at'] = $lockedPayment->refunded_at ?? $this->providerDate(data_get($providerData, 'date_last_updated'));
                if ($refundedAmount !== null && ! $this->calculation->sameAmount($refundedAmount, '0')) {
                    $attributes['refunded_amount'] = $refundedAmount;
                } elseif (in_array($mappedStatus, [PaymentStatus::REFUNDED, PaymentStatus::CHARGED_BACK], true)) {
                    $attributes['refunded_amount'] = (string) $lockedPayment->gross_amount;
                }
            }

            $lockedPayment->forceFill($attributes)->save();
            if ($previousStatus !== $mappedStatus) {
                $lockedPayment->transactions()->create([
                    'event_type' => 'payment.status_changed',
                    'payload' => [
                        'from' => $previousStatus?->value,
                        'to' => $mappedStatus->value,
                        'provider_payment_id' => $providerPaymentId,
                    ],
                ]);
            }
            if ($auxiliaryIssues !== []) {
                $lockedPayment->transactions()->create([
                    'event_type' => $splitMismatch ? 'payment.split_mismatch' : 'provider.data_ignored',
                    'payload' => [
                        'issues' => $auxiliaryIssues,
                        'provider_payment_id' => $providerPaymentId,
                    ],
                ]);
                Log::log($splitMismatch ? 'critical' : 'warning', 'Mercado Pago returned inconsistent auxiliary amounts', [
                    'payment_id' => $lockedPayment->getKey(),
                    'issues' => $auxiliaryIssues,
                ]);
            }

            $isJobPayment = $lockedPayment->kind === PaymentKind::JOB;
            $otherApprovedJobPayment = $isJobPayment && $job->payments()
                ->where('kind', PaymentKind::JOB->value)
                ->where('id', '!=', $lockedPayment->getKey())
                ->where('status', PaymentStatus::APPROVED->value)
                ->exists();

            if ($mappedStatus === PaymentStatus::APPROVED && $isJobPayment && $otherApprovedJobPayment) {
                $duplicateAlreadyReported = $lockedPayment->transactions()
                    ->where('event_type', 'payment.duplicate_approved_detected')
                    ->exists();
                $lockedPayment->transactions()->create([
                    'event_type' => 'payment.duplicate_approved_detected',
                    'payload' => [
                        'reason' => 'another_job_payment_is_already_approved',
                        'provider_payment_id' => $providerPaymentId,
                    ],
                ]);
                Log::critical('Multiple approved payments detected for one job', [
                    'payment_id' => $lockedPayment->getKey(),
                    'job_request_id' => $job->getKey(),
                ]);
                $this->markDuplicateApprovalIncident($lockedPayment, $job, ! $duplicateAlreadyReported);
            } elseif ($mappedStatus === PaymentStatus::APPROVED && $isJobPayment && $splitMismatch) {
                $transitioned = $this->markJobDisputed($job);
                if ($transitioned) {
                    $lockedPayment->client?->notify(new ChambappNotification(
                        'payment_split_review',
                        'Tu pago está en revisión',
                        'El pago fue recibido, pero detectamos una inconsistencia en la distribución. No debes pagar otra vez.',
                        route('job-requests.show', $job),
                    ));
                    $lockedPayment->professional?->user?->notify(new ChambappNotification(
                        'payment_split_review_professional',
                        'Pago en revisión',
                        'No inicies el trabajo hasta que revisemos la distribución del pago.',
                        route('job-requests.show', $job),
                    ));
                }
            } elseif ($mappedStatus === PaymentStatus::APPROVED && $isJobPayment && $job->status === JobStatus::AWAITING_PAYMENT) {
                $job->forceFill(['status' => JobStatus::PAID])->save();
                $lockedPayment->client?->notify(new ChambappNotification(
                    'payment_approved',
                    'Tu pago fue aprobado',
                    'El trabajo ya está contratado dentro de Chambapp.',
                    route('job-requests.show', $job),
                ));
                $lockedPayment->professional?->user?->notify(new ChambappNotification(
                    'payment_approved_professional',
                    'El cliente realizó el pago',
                    'Ya puedes coordinar e iniciar el trabajo.',
                    route('job-requests.show', $job),
                ));
            } elseif ($mappedStatus === PaymentStatus::REJECTED && $previousStatus !== PaymentStatus::REJECTED) {
                $lockedPayment->client?->notify(new ChambappNotification(
                    $isJobPayment ? 'payment_rejected' : 'tip_rejected',
                    $isJobPayment ? 'El pago no pudo ser procesado' : 'La propina no pudo ser procesada',
                    'Puedes intentarlo nuevamente desde Chambapp.',
                    $isJobPayment ? route('client.payments.summary', $job) : route('job-requests.show', $job),
                ));
            }

            if ($isJobPayment && $this->isPaymentRisk($mappedStatus) && ! $otherApprovedJobPayment) {
                if (in_array($job->status, [
                    JobStatus::AWAITING_PAYMENT,
                    JobStatus::PAID,
                    JobStatus::ON_THE_WAY,
                    JobStatus::ARRIVED,
                    JobStatus::IN_PROGRESS,
                    JobStatus::AWAITING_CONFIRMATION,
                    JobStatus::COMPLETED,
                ], true)) {
                    $job->forceFill(['status' => JobStatus::DISPUTED])->save();
                }
                if (! $this->isPaymentRisk($previousStatus)) {
                    $lockedPayment->client?->notify(new ChambappNotification(
                        'payment_reversed',
                        'El pago requiere revisión',
                        'Mercado Pago informó una mediación, reembolso o contracargo. Chambapp detuvo el flujo para revisión.',
                        route('job-requests.show', $job),
                    ));
                    $lockedPayment->professional?->user?->notify(new ChambappNotification(
                        'payment_reversed_professional',
                        'El pago requiere revisión',
                        'El trabajo fue detenido porque el pago cambió de estado.',
                        route('job-requests.show', $job),
                    ));
                }
            }

            return $lockedPayment->fresh();
        });
    }

    public function reconcile(Payment $payment): Payment
    {
        $current = Payment::query()->with('professional')->findOrFail($payment->getKey());
        if ($this->hasBoundFinancialAttempt($current->status) && filled($current->external_payment_id)) {
            $providerPayments = [$this->mercadoPago->getPayment((string) $current->external_payment_id, $current->professional)];
        } else {
            $providerPayments = array_values(array_filter(
                $this->mercadoPago->searchPayments($current),
                static fn (array $candidate): bool => filled(data_get($candidate, 'id')),
            ));
            usort($providerPayments, static function (array $left, array $right): int {
                $priority = static fn (array $item): int => match (strtolower((string) data_get($item, 'status'))) {
                    'approved' => 40,
                    'charged_back', 'refunded', 'partially_refunded', 'in_mediation' => 30,
                    'in_process', 'pending', 'authorized' => 20,
                    'rejected', 'cancelled', 'cancelled_by_user' => 10,
                    default => 0,
                };

                return ($priority($right) <=> $priority($left))
                    ?: strcmp((string) data_get($right, 'date_last_updated'), (string) data_get($left, 'date_last_updated'));
            });
            if ($providerPayments !== []) {
                $canonical = array_shift($providerPayments);
                $canonicalId = (string) data_get($canonical, 'id');
                $duplicateApprovals = array_values(array_filter(
                    $providerPayments,
                    static fn (array $candidate): bool => strtolower((string) data_get($candidate, 'status')) === 'approved'
                        && ! hash_equals($canonicalId, (string) data_get($candidate, 'id')),
                ));
                $providerPayments = [$canonical, ...$duplicateApprovals];
            }
        }

        foreach ($providerPayments as $providerData) {
            $providerPaymentId = (string) data_get($providerData, 'id');
            if ($providerPaymentId === '') {
                continue;
            }
            $eventFingerprint = hash('sha256', implode('|', [
                (string) $current->getKey(),
                $providerPaymentId,
                (string) data_get($providerData, 'status'),
                (string) data_get($providerData, 'date_last_updated'),
            ]));
            $current = $this->applyProviderPayment(
                $current,
                $providerData,
                'reconcile-'.$eventFingerprint,
                [
                    'type' => 'reconciliation',
                    'provider_payment_id' => $providerPaymentId,
                    'provider_status' => (string) data_get($providerData, 'status'),
                ],
            );
        }

        $current->forceFill(['last_reconciled_at' => now()])->save();

        return $current->fresh();
    }

    private function createOrReuseJobPayment(JobRequest $jobRequest, User $client): Payment
    {
        return DB::transaction(function () use ($jobRequest, $client): Payment {
            $job = JobRequest::query()->with(['professional', 'quotes'])->lockForUpdate()->findOrFail($jobRequest->getKey());
            $this->identityVerification->ensureProfessionalCanAcceptJobs($job->professional);
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
            $approved = $job->payments()
                ->where('kind', PaymentKind::JOB->value)
                ->where('status', PaymentStatus::APPROVED->value)
                ->exists();
            if ($approved) {
                throw new DomainException('Este trabajo ya tiene un pago aprobado.');
            }

            $active = $job->payments()
                ->where('kind', PaymentKind::JOB->value)
                ->latest('id')
                ->first();
            if ($active) {
                if ($this->isFinancialLoss($active->status)) {
                    throw new DomainException('Este pago requiere revisión antes de crear otro intento.');
                }

                return $active;
            }

            $money = $this->calculation->forJob($job);
            $payment = $job->payments()->create([
                'client_id' => $job->client_id,
                'professional_id' => $job->professional_id,
                'provider' => config('chambapp.payments.provider'),
                'kind' => PaymentKind::JOB,
                'currency' => $money->currency,
                'economic_model_version' => $money->economicModelVersion,
                'base_amount' => $money->baseAmount,
                'client_service_fee_percent' => $money->clientServiceFeePercent,
                'client_service_fee' => $money->clientServiceFee,
                'professional_commission_percent' => $money->professionalCommissionPercent,
                'professional_commission' => $money->professionalCommission,
                'customer_total' => $money->customerTotal,
                'platform_gross_fee' => $money->platformGrossFee,
                'professional_amount_before_external_costs' => $money->professionalAmountBeforeExternalCosts,
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
                    'kind' => PaymentKind::JOB->value,
                    'currency' => $money->currency,
                    'economic_model_version' => $money->economicModelVersion,
                    'base_amount' => $money->baseAmount,
                    'client_service_fee_percent' => $money->clientServiceFeePercent,
                    'client_service_fee' => $money->clientServiceFee,
                    'professional_commission_percent' => $money->professionalCommissionPercent,
                    'professional_commission' => $money->professionalCommission,
                    'customer_total' => $money->customerTotal,
                    'platform_gross_fee' => $money->platformGrossFee,
                    'professional_amount_before_external_costs' => $money->professionalAmountBeforeExternalCosts,
                    'gross_amount' => $money->grossAmount,
                    'platform_fee_percent' => $money->platformFeePercent,
                    'platform_fee' => $money->platformFee,
                    'professional_amount' => $money->professionalAmount,
                ],
            ]);

            return $payment;
        });
    }

    private function createOrReuseCheckoutPreference(Payment $payment): Payment
    {
        try {
            return DB::transaction(function () use ($payment): Payment {
                $lockedPayment = Payment::query()
                    ->with(['jobRequest.service', 'professional'])
                    ->lockForUpdate()
                    ->findOrFail($payment->getKey());
                $hasReusableCheckout = filled($lockedPayment->checkout_url)
                    && $lockedPayment->checkout_expires_at?->isFuture() === true;
                if ($hasReusableCheckout) {
                    return $lockedPayment->fresh();
                }
                $renewing = filled($lockedPayment->checkout_url);
                if ($renewing && $lockedPayment->status === PaymentStatus::PROCESSING) {
                    throw new DomainException('El pago sigue procesándose. Espera la confirmación antes de intentar de nuevo.');
                }
                if ($renewing) {
                    $lockedPayment->transactions()->create([
                        'event_type' => 'checkout.expired',
                        'payload' => ['preference_id' => $lockedPayment->external_preference_id],
                    ]);
                    $lockedPayment->forceFill([
                        'external_preference_id' => null,
                        'checkout_url' => null,
                        'checkout_expires_at' => null,
                        'status' => PaymentStatus::PENDING,
                    ])->save();
                }

                // This row lock intentionally spans the short provider call. It serializes
                // double-clicks across every PHP process and prevents two live checkout URLs.
                $preference = $this->mercadoPago->createPreference($lockedPayment);
                $lockedPayment->forceFill([
                    'external_preference_id' => $preference['id'],
                    'checkout_url' => $preference['url'],
                    'checkout_expires_at' => $preference['expires_at'] ?? now()->addHours((int) config('chambapp.payments.preference_lifetime_hours', 24)),
                ])->save();
                $lockedPayment->transactions()->create([
                    'event_type' => $renewing ? 'checkout.renewed' : 'checkout.created',
                    'payload' => [
                        'preference_id' => $preference['id'],
                        'external_reference' => $lockedPayment->external_reference,
                    ],
                ]);

                return $lockedPayment->fresh();
            });
        } catch (MercadoPagoException $exception) {
            $this->recordCheckoutFailure($payment, 'provider_unavailable');

            throw $exception;
        } catch (DomainException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            $this->recordCheckoutFailure($payment, 'unexpected_provider_error');

            throw new MercadoPagoException('No pudimos iniciar el pago en este momento. Intenta nuevamente.', 0, $exception);
        }
    }

    private function recordCheckoutFailure(Payment $payment, string $reason): void
    {
        try {
            Payment::query()->find($payment->getKey())?->transactions()->create([
                'event_type' => 'checkout.failed',
                'payload' => ['reason' => $reason],
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function markDuplicateApprovalIncident(Payment $payment, JobRequest $job, bool $notify): void
    {
        $this->markJobDisputed($job);
        if (! $notify) {
            return;
        }

        $payment->client?->notify(new ChambappNotification(
            'payment_duplicate_review',
            'Detectamos más de un cobro',
            'No realices otro pago. El trabajo quedó en revisión para resolver el cobro duplicado.',
            route('job-requests.show', $job),
        ));
        $payment->professional?->user?->notify(new ChambappNotification(
            'payment_duplicate_review_professional',
            'Pago duplicado en revisión',
            'No inicies o continúes el trabajo hasta que se revise el incidente de cobro.',
            route('job-requests.show', $job),
        ));
    }

    private function markJobDisputed(JobRequest $job): bool
    {
        if ($job->status === JobStatus::DISPUTED) {
            return false;
        }

        $job->forceFill(['status' => JobStatus::DISPUTED])->save();

        return true;
    }

    private function normalizeExternalAmount(mixed $amount, ?string $maximum = null): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        try {
            $normalized = $this->calculation->normalize((string) $amount);
            if ($maximum !== null && ! $this->calculation->isAtMost($normalized, $maximum)) {
                return null;
            }

            return $normalized;
        } catch (DomainException) {
            return null;
        }
    }

    private function hasBoundFinancialAttempt(PaymentStatus $status): bool
    {
        return in_array($status, [
            PaymentStatus::APPROVED,
            PaymentStatus::IN_MEDIATION,
            PaymentStatus::PARTIALLY_REFUNDED,
            PaymentStatus::REFUNDED,
            PaymentStatus::CHARGED_BACK,
        ], true);
    }

    private function isFinancialLoss(PaymentStatus $status): bool
    {
        return in_array($status, [
            PaymentStatus::PARTIALLY_REFUNDED,
            PaymentStatus::REFUNDED,
            PaymentStatus::CHARGED_BACK,
        ], true);
    }

    private function isPaymentRisk(PaymentStatus $status): bool
    {
        return $status === PaymentStatus::IN_MEDIATION || $this->isFinancialLoss($status);
    }

    private function isTerminalRegression(PaymentStatus $from, PaymentStatus $to): bool
    {
        return match ($from) {
            PaymentStatus::APPROVED => ! in_array($to, [
                PaymentStatus::APPROVED,
                PaymentStatus::IN_MEDIATION,
                PaymentStatus::PARTIALLY_REFUNDED,
                PaymentStatus::REFUNDED,
                PaymentStatus::CHARGED_BACK,
            ], true),
            PaymentStatus::IN_MEDIATION => ! in_array($to, [
                PaymentStatus::APPROVED,
                PaymentStatus::IN_MEDIATION,
                PaymentStatus::PARTIALLY_REFUNDED,
                PaymentStatus::REFUNDED,
                PaymentStatus::CHARGED_BACK,
            ], true),
            PaymentStatus::PARTIALLY_REFUNDED => ! in_array($to, [
                PaymentStatus::IN_MEDIATION,
                PaymentStatus::PARTIALLY_REFUNDED,
                PaymentStatus::REFUNDED,
                PaymentStatus::CHARGED_BACK,
            ], true),
            PaymentStatus::REFUNDED => ! in_array($to, [PaymentStatus::REFUNDED, PaymentStatus::CHARGED_BACK], true),
            PaymentStatus::CHARGED_BACK => $to !== PaymentStatus::CHARGED_BACK,
            default => false,
        };
    }

    private function providerDate(mixed $value): mixed
    {
        if (filled($value)) {
            try {
                return Date::parse((string) $value);
            } catch (Throwable) {
                // Fall through to receipt time if the provider ever sends a malformed date.
            }
        }

        return now();
    }
}
