<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('payments', 'platform_fee_percent')) {
            Schema::table('payments', fn (Blueprint $table) => $table->decimal('platform_fee_percent', 5, 2)->default(15.00)->after('gross_amount'));
        }
        if (! Schema::hasColumn('payments', 'external_preference_id')) {
            Schema::table('payments', fn (Blueprint $table) => $table->string('external_preference_id')->nullable()->after('external_payment_id')->index());
        }
        if (! Schema::hasColumn('payments', 'checkout_url')) {
            Schema::table('payments', fn (Blueprint $table) => $table->text('checkout_url')->nullable()->after('external_preference_id'));
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            Schema::table('payments', function (Blueprint $table): void {
                $table->dropUnique('payments_job_request_id_unique');
                $table->unique('external_reference');
                $table->decimal('provider_fee', 12, 2)->nullable()->default(null)->change();
            });
            return;
        }

        // MySQL cannot remove a unique index while it is backing the FK.
        $indexes = DB::select("SELECT INDEX_NAME, NON_UNIQUE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments'");
        $hasJobUnique = collect($indexes)->contains(fn ($index) => $index->INDEX_NAME === 'payments_job_request_id_unique' && (int) $index->NON_UNIQUE === 0);
        $hasReferenceUnique = collect($indexes)->contains(fn ($index) => $index->INDEX_NAME === 'payments_external_reference_unique' && (int) $index->NON_UNIQUE === 0);
        if ($hasJobUnique) {
            Schema::table('payments', function (Blueprint $table): void {
                $table->dropForeign(['job_request_id']);
                $table->dropUnique('payments_job_request_id_unique');
                $table->foreign('job_request_id')->references('id')->on('job_requests')->restrictOnDelete();
            });
        }
        if (! $hasReferenceUnique) {
            $hasReferenceIndex = collect($indexes)->contains(fn ($index) => $index->INDEX_NAME === 'payments_external_reference_index');
            Schema::table('payments', function (Blueprint $table) use ($hasReferenceIndex): void {
                if ($hasReferenceIndex) {
                    $table->dropIndex('payments_external_reference_index');
                }
                $table->unique('external_reference');
            });
        }

        Schema::table('payments', function (Blueprint $table): void {
            $table->decimal('provider_fee', 12, 2)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropUnique(['external_reference']);
            $table->dropIndex(['external_preference_id']);
            $table->dropColumn(['platform_fee_percent', 'external_preference_id', 'checkout_url']);
            $table->decimal('provider_fee', 12, 2)->default(0)->nullable(false)->change();
            $table->unique('job_request_id');
        });
    }
};
