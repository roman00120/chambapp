<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commerce_orders', function (Blueprint $table): void {
            $table->string('provider', 40)->default('mercadopago')->after('currency');
            $table->string('financial_status', 30)->default('pending')->after('provider')->index();
            $table->string('external_payment_id')->nullable()->after('external_preference_id');
            $table->decimal('refunded_amount', 12, 2)->default(0)->after('paid_at');
            $table->timestamp('provider_updated_at')->nullable()->after('refunded_amount');
            $table->timestamp('refunded_at')->nullable()->after('provider_updated_at');
            $table->timestamp('last_reconciled_at')->nullable()->after('refunded_at')->index();
            $table->text('fulfillment_error')->nullable()->after('last_reconciled_at');
            $table->unique(['provider', 'external_payment_id'], 'commerce_provider_payment_unique');
            $table->index(['financial_status', 'status'], 'commerce_financial_fulfillment_index');
        });

        DB::table('commerce_orders')
            ->where('status', 'approved')
            ->update(['financial_status' => 'approved']);

        // Legacy checkout URLs have no trustworthy provider expiration. Force a safe renewal.
        DB::table('commerce_orders')
            ->where('status', 'pending')
            ->whereNotNull('external_preference_id')
            ->whereNotNull('checkout_url')
            ->whereNull('checkout_expires_at')
            ->update(['checkout_expires_at' => now()->subSecond()]);

        Schema::create('commerce_order_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('commerce_order_id')->constrained('commerce_orders')->cascadeOnDelete();
            $table->string('event_type', 60);
            $table->string('provider_event_id')->nullable()->unique();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['commerce_order_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_order_events');

        Schema::table('commerce_orders', function (Blueprint $table): void {
            $table->dropUnique('commerce_provider_payment_unique');
            $table->dropIndex('commerce_financial_fulfillment_index');
            $table->dropIndex(['financial_status']);
            $table->dropIndex(['last_reconciled_at']);
            $table->dropColumn([
                'provider',
                'financial_status',
                'external_payment_id',
                'refunded_amount',
                'provider_updated_at',
                'refunded_at',
                'last_reconciled_at',
                'fulfillment_error',
            ]);
        });
    }
};
