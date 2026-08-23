<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicatePaymentIds = DB::table('payments')
            ->select(['provider', 'external_payment_id'])
            ->whereNotNull('provider')
            ->whereNotNull('external_payment_id')
            ->groupBy('provider', 'external_payment_id')
            ->havingRaw('COUNT(*) > 1')
            ->limit(5)
            ->get();
        $duplicatePreferenceIds = DB::table('payments')
            ->select(['provider', 'external_preference_id'])
            ->whereNotNull('provider')
            ->whereNotNull('external_preference_id')
            ->groupBy('provider', 'external_preference_id')
            ->havingRaw('COUNT(*) > 1')
            ->limit(5)
            ->get();
        if ($duplicatePaymentIds->isNotEmpty() || $duplicatePreferenceIds->isNotEmpty()) {
            throw new RuntimeException('No se puede asegurar la integridad de pagos: existen identificadores de proveedor duplicados.');
        }

        Schema::table('payments', function (Blueprint $table): void {
            $table->string('kind', 20)->default('job')->after('provider');
            $table->decimal('refunded_amount', 12, 2)->default(0)->after('refunded_at');
            $table->timestamp('checkout_expires_at')->nullable()->after('checkout_url');
            $table->timestamp('last_reconciled_at')->nullable()->after('refunded_amount')->index();
            $table->index(['job_request_id', 'kind', 'status'], 'payments_job_kind_status_index');
            $table->unique(['provider', 'external_payment_id'], 'payments_provider_payment_unique');
            $table->unique(['provider', 'external_preference_id'], 'payments_provider_preference_unique');
        });

        DB::table('payments')
            ->where('tip_amount', '>', 0)
            ->update(['kind' => 'tip']);

    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropUnique('payments_provider_payment_unique');
            $table->dropUnique('payments_provider_preference_unique');
            $table->dropIndex('payments_job_kind_status_index');
            $table->dropIndex(['last_reconciled_at']);
            $table->dropColumn(['kind', 'refunded_amount', 'checkout_expires_at', 'last_reconciled_at']);
        });
    }
};
