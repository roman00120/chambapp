<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile
        {--limit=100 : Maximum payments to query per run}
        {--days=30 : Only payments created within this lookback window}
        {--settled : Recheck approved and disputed payments instead of active checkout attempts}';

    protected $description = 'Reconcile non-terminal Mercado Pago payments and recent approvals';

    public function handle(PaymentService $payments): int
    {
        $limit = max(1, min(500, (int) $this->option('limit')));
        $days = max(1, min(180, (int) $this->option('days')));
        $statuses = $this->option('settled')
            ? [
                PaymentStatus::APPROVED->value,
                PaymentStatus::IN_MEDIATION->value,
                PaymentStatus::PARTIALLY_REFUNDED->value,
            ]
            : [
                PaymentStatus::PENDING->value,
                PaymentStatus::PROCESSING->value,
                PaymentStatus::REJECTED->value,
                PaymentStatus::CANCELLED->value,
            ];
        $candidates = Payment::query()
            ->where('provider', config('chambapp.payments.provider', 'mercadopago'))
            ->whereNotNull('external_reference')
            ->whereIn('status', $statuses)
            ->whereHas('professional', fn ($query) => $query
                ->whereNotNull('mercadopago_user_id')
                ->whereNotNull('mercadopago_access_token')
                ->where(function ($token) {
                    $token->whereNull('mercadopago_token_expires_at')
                        ->orWhere('mercadopago_token_expires_at', '>', now())
                        ->orWhereNotNull('mercadopago_refresh_token');
                }))
            ->where('created_at', '>=', now()->subDays($days))
            ->where('updated_at', '<=', now()->subMinutes(2))
            ->orderByRaw('CASE WHEN last_reconciled_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('last_reconciled_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $failures = 0;
        foreach ($candidates as $payment) {
            try {
                $payments->reconcile($payment);
            } catch (Throwable $exception) {
                $failures++;
                try {
                    $payment->forceFill(['last_reconciled_at' => now()])->save();
                } catch (Throwable) {
                    // The next scheduler run will retry if even the backoff timestamp cannot be persisted.
                }
                Log::warning('Payment reconciliation failed', [
                    'payment_id' => $payment->getKey(),
                    'exception' => $exception::class,
                ]);
            }
        }

        $this->info(sprintf(
            'Reconciled %d payment(s); %d failed.',
            $candidates->count() - $failures,
            $failures,
        ));

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }
}
