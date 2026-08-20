<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_quotes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_request_id')->constrained('job_requests')->cascadeOnDelete();
            $table->foreignId('professional_id')->constrained('professional_profiles')->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('description', 300);
            $table->string('status', 30)->default('pending')->index();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->string('rejection_reason', 140)->nullable();
            $table->timestamps();

            $table->index(['job_request_id', 'status']);
            $table->index(['professional_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_quotes');
    }
};
