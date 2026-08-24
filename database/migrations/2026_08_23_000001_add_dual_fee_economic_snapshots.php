<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_requests', function (Blueprint $table) {
            $table->string('economic_model_version', 50)->nullable()->after('agreed_price');
            $table->decimal('base_amount', 12, 2)->nullable()->after('economic_model_version');
            $table->decimal('client_service_fee_percent', 5, 2)->nullable()->after('base_amount');
            $table->decimal('client_service_fee', 12, 2)->nullable()->after('client_service_fee_percent');
            $table->decimal('professional_commission_percent', 5, 2)->nullable()->after('client_service_fee');
            $table->decimal('professional_commission', 12, 2)->nullable()->after('professional_commission_percent');
            $table->decimal('customer_total', 12, 2)->nullable()->after('professional_commission');
            $table->decimal('platform_gross_fee', 12, 2)->nullable()->after('customer_total');
            $table->decimal('professional_amount_before_external_costs', 12, 2)->nullable()->after('platform_gross_fee');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('economic_model_version', 50)->nullable()->after('currency');
            $table->decimal('base_amount', 12, 2)->nullable()->after('economic_model_version');
            $table->decimal('client_service_fee_percent', 5, 2)->nullable()->after('base_amount');
            $table->decimal('client_service_fee', 12, 2)->nullable()->after('client_service_fee_percent');
            $table->decimal('professional_commission_percent', 5, 2)->nullable()->after('client_service_fee');
            $table->decimal('professional_commission', 12, 2)->nullable()->after('professional_commission_percent');
            $table->decimal('customer_total', 12, 2)->nullable()->after('professional_commission');
            $table->decimal('platform_gross_fee', 12, 2)->nullable()->after('customer_total');
            $table->decimal('professional_amount_before_external_costs', 12, 2)->nullable()->after('platform_gross_fee');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'economic_model_version', 'base_amount', 'client_service_fee_percent',
                'client_service_fee', 'professional_commission_percent',
                'professional_commission', 'customer_total', 'platform_gross_fee',
                'professional_amount_before_external_costs',
            ]);
        });

        Schema::table('job_requests', function (Blueprint $table) {
            $table->dropColumn([
                'economic_model_version', 'base_amount', 'client_service_fee_percent',
                'client_service_fee', 'professional_commission_percent',
                'professional_commission', 'customer_total', 'platform_gross_fee',
                'professional_amount_before_external_costs',
            ]);
        });
    }
};
