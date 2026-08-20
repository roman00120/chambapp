<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->decimal('platform_fee_percent', 5, 2)->default(15.00)->after('gross_amount');
            $table->string('external_preference_id')->nullable()->after('external_payment_id')->index();
            $table->text('checkout_url')->nullable()->after('external_preference_id');
            $table->dropUnique('payments_job_request_id_unique');
            $table->unique('external_reference');
        });

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
