<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Exceptions\CommerceFulfillmentException;
use App\Models\CommerceOrder;
use App\Models\ProfessionalProfile;
use App\Models\Service;
use DomainException;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CommerceService
{
    public function __construct(
        private readonly MercadoPagoService $mercadoPago,
        private readonly PaymentCalculationService $calculations,
        private readonly PaymentStatusMapper $statusMapper,
    ) {}

    public function createFeaturedOrder(ProfessionalProfile $professional, Service $service, int $days): CommerceOrder
    {
        abort_unless($service->professional_id === $professional->getKey(), 403);
        $prices = config('chambapp.commerce.featured_prices', []);
        if (! isset($prices[$days])) {
            throw new DomainException('La duración de la promoción no es válida.');
        }

        return $this->createOrder($professional, 'featured', $service, 'featured-'.$days, (string) $prices[$days], ['days' => $days]);
    }

    public function createCustomizationOrder(ProfessionalProfile $professional, string $itemKey): CommerceOrder
    {
        $item = config('chambapp.commerce.store_items.'.$itemKey);
        if (! is_array($item)
            || ! isset($item['kind'], $item['name'], $item['price'], $item['value'])
            || ! in_array($item['kind'], ['theme', 'banner', 'frame', 'animation'], true)
            || ! is_string($item['name'])
            || ! is_string($item['value'])
            || $item['name'] === ''
            || $item['value'] === '') {
            throw new DomainException('El artículo de la tienda no existe.');
        }

        return $this->createOrder($professional, 'customization', null, $itemKey, (string) $item['price'], $item);
    }

    public function checkout(CommerceOrder $order): CommerceOrder
    {
        $snapshot = CommerceOrder::query()->findOrFail($order->getKey());
        if (filled($snapshot->external_preference_id) && $snapshot->checkout_expires_at?->isPast()) {
            // A payment started just before expiry may only be visible after the webhook.
            // Reconcile before opening a new preference for the same external reference.
            $this->reconcile($snapshot);
        }

        return DB::transaction(function () use ($order): CommerceOrder {
            $order = CommerceOrder::query()->lockForUpdate()->findOrFail($order->getKey());
            $this->assertCheckoutCanBeStarted($order);

            if (filled($order->external_preference_id) && ! filled($order->checkout_url)) {
                throw new DomainException('La compra tiene una preferencia incompleta. No generes otro cobro; contacta a soporte.');
            }

            if (filled($order->checkout_url)
                && ($order->checkout_expires_at === null || $order->checkout_expires_at->isFuture())) {
                // Preferences created before expiry tracking are intentionally kept. Creating a
                // second preference for a legacy external reference can lead to two charges.
                return $order;
            }

            $renewing = filled($order->checkout_url);
            if ($renewing) {
                $graceMinutes = max(0, (int) config('chambapp.payments.checkout_renewal_grace_minutes', 15));
                if ($order->checkout_expires_at?->copy()->addMinutes($graceMinutes)->isFuture()) {
                    throw new DomainException('El enlace acaba de vencer. Espera unos minutos mientras confirmamos si Mercado Pago terminó de procesar el intento.');
                }

                $order->events()->create([
                    'event_type' => 'checkout.expired',
                    'payload' => ['preference_id' => $order->external_preference_id],
                ]);
                $order->forceFill([
                    'external_preference_id' => null,
                    'checkout_url' => null,
                    'checkout_expires_at' => null,
                    'external_payment_id' => null,
                    'financial_status' => PaymentStatus::PENDING,
                ])->save();
            }

            $item = $order->metadata ?? [];
            $preference = $this->mercadoPago->createPlatformPreference(
                (string) ($item['name'] ?? $order->item_key),
                (string) $order->amount,
                (string) $order->external_reference,
            );
            $order->forceFill([
                'external_preference_id' => $preference['id'],
                'checkout_url' => $preference['url'],
                'checkout_expires_at' => $preference['expires_at'] ?? now()->addHours((int) config('chambapp.payments.preference_lifetime_hours', 24)),
                'financial_status' => PaymentStatus::PENDING,
            ])->save();
            $order->events()->create([
                'event_type' => $renewing ? 'checkout.renewed' : 'checkout.created',
                'payload' => [
                    'preference_id' => $preference['id'],
                    'external_reference' => $order->external_reference,
                ],
            ]);

            return $order->fresh();
        });
    }

    /**
     * Apply a provider payment after the caller has fetched it directly from Mercado Pago.
     * A fulfillment error is deliberately retryable: acknowledging it would lose a valid sale.
     *
     * @param  array<string, mixed>  $providerData
     * @param  array<string, mixed>  $auditPayload
     */
    public function applyProviderPayment(
        CommerceOrder $order,
        array $providerData,
        ?string $providerEventId,
        array $auditPayload = [],
    ): CommerceOrder {
        $retryableFailure = null;

        $result = DB::transaction(function () use ($order, $providerData, $providerEventId, $auditPayload, &$retryableFailure): CommerceOrder {
            $snapshot = CommerceOrder::query()->findOrFail($order->getKey());
            $professional = ProfessionalProfile::query()->lockForUpdate()->findOrFail($snapshot->professional_id);
            $service = $snapshot->service_id === null
                ? null
                : Service::withTrashed()->lockForUpdate()->find($snapshot->service_id);
            $lockedOrder = CommerceOrder::query()
                ->with('events')
                ->lockForUpdate()
                ->findOrFail($snapshot->getKey());

            $providerPaymentId = (string) data_get($providerData, 'id');
            $providerEventId = $this->providerEventId($lockedOrder, $providerData, $providerEventId);
            if ($providerEventId && $lockedOrder->events()->where('provider_event_id', $providerEventId)->exists()) {
                return $lockedOrder->fresh();
            }

            $lockedOrder->events()->create([
                'event_type' => 'webhook.received',
                'provider_event_id' => $providerEventId,
                'payload' => $auditPayload,
            ]);

            $mismatchReason = $this->providerMismatchReason($lockedOrder, $providerData);
            if ($mismatchReason !== null) {
                $lockedOrder->events()->create([
                    'event_type' => 'webhook.rejected',
                    'payload' => [
                        'reason' => $mismatchReason,
                        'provider_payment_id' => $providerPaymentId,
                    ],
                ]);

                return $lockedOrder->fresh();
            }

            $mappedStatus = $this->providerStatus($lockedOrder, $providerData);
            $previousStatus = $lockedOrder->financial_status;
            if (filled($lockedOrder->external_payment_id)
                && ! hash_equals((string) $lockedOrder->external_payment_id, $providerPaymentId)
                && $this->hasBoundFinancialAttempt($previousStatus)) {
                $eventType = $mappedStatus === PaymentStatus::APPROVED
                    ? 'payment.duplicate_approved_detected'
                    : 'webhook.ignored';
                $lockedOrder->events()->create([
                    'event_type' => $eventType,
                    'payload' => [
                        'reason' => 'different_provider_payment_after_financial_state',
                        'bound_provider_payment_id' => (string) $lockedOrder->external_payment_id,
                        'incoming_provider_payment_id' => $providerPaymentId,
                        'incoming_status' => $mappedStatus->value,
                    ],
                ]);
                if ($mappedStatus === PaymentStatus::APPROVED) {
                    $lockedOrder->forceFill([
                        'status' => 'review',
                        'fulfillment_error' => 'Se detectó más de un cobro aprobado para la misma compra.',
                    ])->save();
                    Log::critical('Duplicate Mercado Pago approval detected for commerce order', [
                        'commerce_order_id' => $lockedOrder->getKey(),
                    ]);
                }

                return $lockedOrder->fresh();
            }

            if ($this->isTerminalRegression($previousStatus, $mappedStatus)) {
                $lockedOrder->events()->create([
                    'event_type' => 'webhook.ignored',
                    'payload' => [
                        'reason' => 'financial_status_regression',
                        'from' => $previousStatus->value,
                        'to' => $mappedStatus->value,
                        'provider_payment_id' => $providerPaymentId,
                    ],
                ]);

                return $lockedOrder->fresh();
            }

            $attributes = [
                'external_payment_id' => $providerPaymentId,
                'financial_status' => $mappedStatus,
                'provider_updated_at' => $this->providerDate(data_get($providerData, 'date_last_updated')),
            ];
            if ($mappedStatus === PaymentStatus::APPROVED) {
                $attributes['paid_at'] = $lockedOrder->paid_at ?? $this->providerDate(data_get($providerData, 'date_approved'));
            }
            if ($this->isFinancialLoss($mappedStatus)) {
                $attributes['refunded_at'] = $lockedOrder->refunded_at ?? $this->providerDate(data_get($providerData, 'date_last_updated'));
                $attributes['refunded_amount'] = $this->refundedAmount($lockedOrder, $providerData, $mappedStatus);
            }
            $lockedOrder->forceFill($attributes)->save();

            if ($previousStatus !== $mappedStatus) {
                $lockedOrder->events()->create([
                    'event_type' => 'payment.status_changed',
                    'payload' => [
                        'from' => $previousStatus->value,
                        'to' => $mappedStatus->value,
                        'provider_payment_id' => $providerPaymentId,
                    ],
                ]);
            }

            if ($this->isPaymentRisk($mappedStatus)) {
                $lockedOrder->forceFill([
                    'status' => 'review',
                    'fulfillment_error' => 'Mercado Pago informó una mediación, reembolso o contracargo; la compra requiere revisión.',
                ])->save();
                $lockedOrder->events()->create([
                    'event_type' => 'payment.requires_review',
                    'payload' => ['provider_payment_id' => $providerPaymentId, 'status' => $mappedStatus->value],
                ]);

                return $lockedOrder->fresh();
            }

            if ($mappedStatus !== PaymentStatus::APPROVED || $lockedOrder->status === 'approved') {
                return $lockedOrder->fresh();
            }

            try {
                $this->fulfill($lockedOrder, $professional, $service);
                $lockedOrder->forceFill([
                    'status' => 'approved',
                    'paid_at' => $lockedOrder->paid_at ?? now(),
                    'fulfillment_error' => null,
                ])->save();
                $lockedOrder->events()->create([
                    'event_type' => 'fulfillment.completed',
                    'payload' => ['provider_payment_id' => $providerPaymentId],
                ]);
            } catch (Throwable $exception) {
                report($exception);
                $lockedOrder->forceFill([
                    'status' => 'fulfillment_pending',
                    'fulfillment_error' => $this->safeFulfillmentError($exception),
                ])->save();
                $lockedOrder->events()->create([
                    'event_type' => 'fulfillment.failed',
                    'payload' => [
                        'provider_payment_id' => $providerPaymentId,
                        'exception' => $exception::class,
                    ],
                ]);
                $retryableFailure = new CommerceFulfillmentException(
                    'El cobro fue aprobado, pero la compra aún no pudo aplicarse de forma segura.',
                    0,
                    $exception,
                );
            }

            return $lockedOrder->fresh();
        });

        if ($retryableFailure) {
            throw $retryableFailure;
        }

        return $result;
    }

    public function applyPaidOrder(CommerceOrder $order): CommerceOrder
    {
        return DB::transaction(function () use ($order): CommerceOrder {
            $snapshot = CommerceOrder::query()->findOrFail($order->getKey());
            $professional = ProfessionalProfile::query()->lockForUpdate()->findOrFail($snapshot->professional_id);
            $service = $snapshot->service_id === null
                ? null
                : Service::withTrashed()->lockForUpdate()->find($snapshot->service_id);
            $lockedOrder = CommerceOrder::query()->lockForUpdate()->findOrFail($snapshot->getKey());
            if ($lockedOrder->status === 'approved') {
                return $lockedOrder;
            }

            $this->fulfill($lockedOrder, $professional, $service);
            $lockedOrder->forceFill([
                'financial_status' => PaymentStatus::APPROVED,
                'status' => 'approved',
                'paid_at' => $lockedOrder->paid_at ?? now(),
                'fulfillment_error' => null,
            ])->save();
            $lockedOrder->events()->create([
                'event_type' => 'fulfillment.completed',
                'payload' => ['source' => 'manual'],
            ]);

            return $lockedOrder->fresh();
        });
    }

    public function reconcile(CommerceOrder $order): CommerceOrder
    {
        $current = CommerceOrder::query()->findOrFail($order->getKey());
        if ($this->hasBoundFinancialAttempt($current->financial_status) && filled($current->external_payment_id)) {
            $providerPayments = [$this->mercadoPago->getPlatformPayment((string) $current->external_payment_id)];
        } else {
            $providerPayments = $this->canonicalProviderPayments(
                $this->mercadoPago->searchPlatformPayments($current),
            );
        }

        foreach ($providerPayments as $providerData) {
            $providerPaymentId = (string) data_get($providerData, 'id');
            if ($providerPaymentId === '') {
                continue;
            }
            $fingerprint = hash('sha256', implode('|', [
                (string) $current->getKey(),
                $providerPaymentId,
                (string) data_get($providerData, 'status'),
                (string) data_get($providerData, 'date_last_updated'),
            ]));
            $current = $this->applyProviderPayment(
                $current,
                $providerData,
                'reconcile-'.$fingerprint,
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

    private function createOrder(ProfessionalProfile $professional, string $kind, ?Service $service, string $itemKey, string $amount, array $metadata): CommerceOrder
    {
        return DB::transaction(function () use ($professional, $kind, $service, $itemKey, $amount, $metadata): CommerceOrder {
            $professional = ProfessionalProfile::query()->lockForUpdate()->findOrFail($professional->getKey());
            $currency = strtoupper((string) config('chambapp.payments.currency', 'MXN'));
            $amount = $this->calculations->calculate($amount, '0')->grossAmount;
            if (! preg_match('/^[A-Z]{3}$/', $currency)) {
                throw new DomainException('La moneda configurada no es válida.');
            }

            if ($service) {
                $service = Service::query()->lockForUpdate()->findOrFail($service->getKey());
                abort_unless($service->professional_id === $professional->getKey(), 403);
                if ($kind === 'featured' && ! $service->is_active) {
                    throw new DomainException('Solo puedes promocionar un servicio activo.');
                }
            }

            $existing = CommerceOrder::query()
                ->where('professional_id', $professional->getKey())
                ->where('kind', $kind)
                ->where('service_id', $service?->getKey())
                ->where('item_key', $itemKey)
                ->where('amount', $amount)
                ->where('currency', $currency)
                ->whereIn('status', ['pending', 'fulfillment_pending', 'review'])
                ->latest('id')
                ->lockForUpdate()
                ->get()
                ->first(fn (CommerceOrder $candidate): bool => $candidate->metadata === $metadata);
            if ($existing) {
                return $existing;
            }

            return CommerceOrder::create([
                'professional_id' => $professional->getKey(),
                'kind' => $kind,
                'service_id' => $service?->getKey(),
                'item_key' => $itemKey,
                'amount' => $amount,
                'currency' => $currency,
                'provider' => config('chambapp.payments.provider', 'mercadopago'),
                'financial_status' => PaymentStatus::PENDING,
                'status' => 'pending',
                'external_reference' => 'CHAMBAPP-COM-'.$professional->getKey().'-'.str()->random(12),
                'metadata' => $metadata,
            ]);
        });
    }

    private function assertCheckoutCanBeStarted(CommerceOrder $order): void
    {
        if ($order->status !== 'pending') {
            throw new DomainException('Esta compra requiere revisión antes de intentar otro pago.');
        }
        if ($order->financial_status === PaymentStatus::PROCESSING) {
            throw new DomainException('El pago sigue procesándose. Espera la confirmación antes de intentar de nuevo.');
        }
        if ($order->financial_status === PaymentStatus::APPROVED || $this->isPaymentRisk($order->financial_status)) {
            throw new DomainException('Esta compra ya recibió un pago y requiere revisión antes de crear otro intento.');
        }
    }

    private function fulfill(CommerceOrder $order, ProfessionalProfile $professional, ?Service $service): void
    {
        if (! in_array($order->kind, ['featured', 'customization'], true)) {
            throw new DomainException('El tipo de compra no es válido.');
        }

        if ($order->kind === 'featured') {
            $days = (int) data_get($order->metadata, 'days');
            if (! $service
                || $service->professional_id !== $professional->getKey()
                || $days < 1
                || $days > 365) {
                throw new DomainException('No fue posible aplicar la promoción comprada.');
            }
            $start = $service->featured_until?->isFuture() ? $service->featured_until : now();
            $service->forceFill([
                'is_featured' => true,
                'featured_until' => $start->copy()->addDays($days),
            ])->save();

            $professional->user?->notify(new \App\Notifications\PromotionActivatedNotification($service, $days));

            return;
        }

        $this->applyCustomization($professional, $order->metadata ?? []);
    }

    private function applyCustomization(ProfessionalProfile $professional, array $item): void
    {
        $field = match ($item['kind'] ?? null) {
            'theme' => 'profile_theme',
            'banner' => 'profile_banner',
            'frame' => 'profile_frame',
            'animation' => 'profile_animation',
            default => null,
        };
        $value = $item['value'] ?? null;
        if (! $field || ! is_string($value) || $value === '') {
            throw new DomainException('No fue posible aplicar la personalización comprada.');
        }

        $professional->forceFill([$field => $value])->save();
    }

    private function providerEventId(CommerceOrder $order, array $providerData, ?string $eventId): ?string
    {
        $eventId = trim((string) $eventId);
        if ($eventId !== '') {
            return $eventId;
        }
        $providerPaymentId = (string) data_get($providerData, 'id');
        if ($providerPaymentId === '') {
            return null;
        }

        return 'provider-'.hash('sha256', implode('|', [
            (string) $order->getKey(),
            $providerPaymentId,
            (string) data_get($providerData, 'status'),
            (string) data_get($providerData, 'date_last_updated'),
        ]));
    }

    private function providerMismatchReason(CommerceOrder $order, array $providerData): ?string
    {
        $providerPaymentId = (string) data_get($providerData, 'id');
        $amount = $this->normalizeExternalAmount(data_get($providerData, 'transaction_amount'));
        $liveMode = filter_var(data_get($providerData, 'live_mode'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $expectedCollector = (string) config('services.mercadopago.user_id');

        return match (true) {
            $providerPaymentId === '' => 'provider_payment_id_missing',
            ! hash_equals((string) $order->external_reference, (string) data_get($providerData, 'external_reference', '')) => 'reference_mismatch',
            $amount === null || ! $this->calculations->sameAmount($amount, (string) $order->amount) => 'amount_mismatch',
            strtoupper((string) data_get($providerData, 'currency_id')) !== strtoupper((string) $order->currency) => 'currency_mismatch',
            $liveMode === null || $liveMode !== app()->environment('production') => 'environment_mismatch',
            $expectedCollector === '' || ! filled(data_get($providerData, 'collector_id'))
                || ! hash_equals($expectedCollector, (string) data_get($providerData, 'collector_id')) => 'collector_mismatch',
            default => null,
        };
    }

    private function providerStatus(CommerceOrder $order, array $providerData): PaymentStatus
    {
        $status = $this->statusMapper->map((string) data_get($providerData, 'status'));
        $refunded = $this->normalizeExternalAmount(data_get($providerData, 'transaction_amount_refunded'), (string) $order->amount);
        if ($status !== PaymentStatus::CHARGED_BACK && $refunded !== null && ! $this->calculations->sameAmount($refunded, '0')) {
            return $this->calculations->sameAmount($refunded, (string) $order->amount)
                ? PaymentStatus::REFUNDED
                : PaymentStatus::PARTIALLY_REFUNDED;
        }

        return $status;
    }

    private function refundedAmount(CommerceOrder $order, array $providerData, PaymentStatus $status): string
    {
        $refunded = $this->normalizeExternalAmount(data_get($providerData, 'transaction_amount_refunded'), (string) $order->amount);
        if ($refunded !== null && ! $this->calculations->sameAmount($refunded, '0')) {
            return $refunded;
        }

        return in_array($status, [PaymentStatus::REFUNDED, PaymentStatus::CHARGED_BACK], true)
            ? (string) $order->amount
            : (string) $order->refunded_amount;
    }

    /** @param array<int, array<string, mixed>> $providerPayments */
    private function canonicalProviderPayments(array $providerPayments): array
    {
        $providerPayments = array_values(array_filter(
            $providerPayments,
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
        if ($providerPayments === []) {
            return [];
        }

        $canonical = array_shift($providerPayments);
        $canonicalId = (string) data_get($canonical, 'id');
        $duplicateApprovals = array_values(array_filter(
            $providerPayments,
            static fn (array $candidate): bool => strtolower((string) data_get($candidate, 'status')) === 'approved'
                && ! hash_equals($canonicalId, (string) data_get($candidate, 'id')),
        ));

        return [$canonical, ...$duplicateApprovals];
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

    private function normalizeExternalAmount(mixed $amount, ?string $maximum = null): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        try {
            $normalized = $this->calculations->normalize((string) $amount);
            if ($maximum !== null && ! $this->calculations->isAtMost($normalized, $maximum)) {
                return null;
            }

            return $normalized;
        } catch (DomainException) {
            return null;
        }
    }

    private function providerDate(mixed $value): mixed
    {
        if (filled($value)) {
            try {
                return Date::parse((string) $value);
            } catch (Throwable) {
                // Provider timestamps are audit metadata; receipt time is safer than failing a webhook.
            }
        }

        return now();
    }

    private function safeFulfillmentError(Throwable $exception): string
    {
        return mb_strimwidth($exception->getMessage() ?: 'No fue posible aplicar la compra.', 0, 1000);
    }
}
