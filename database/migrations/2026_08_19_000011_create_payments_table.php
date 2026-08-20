<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_request_id')->unique()->constrained('job_requests')->restrictOnDelete();
            $table->foreignId('client_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('professional_id')->constrained('professional_profiles')->restrictOnDelete();
            $table->string('provider')->nullable();
            $table->string('external_payment_id')->nullable()->index();
            $table->string('external_reference')->nullable()->index();
            $table->char('currency', 3)->default('MXN');
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('provider_fee', 12, 2)->default(0);
            $table->decimal('professional_amount', 12, 2);
            $table->string('status', 30)->default('pending')->index();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('refunded_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['professional_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
