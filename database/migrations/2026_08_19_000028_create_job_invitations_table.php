<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_request_id')->constrained('job_requests')->cascadeOnDelete();
            $table->foreignId('professional_id')->constrained('professional_profiles')->restrictOnDelete();
            $table->decimal('distance_km', 7, 2);
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('invited_at')->useCurrent();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
            $table->unique(['job_request_id', 'professional_id']);
            $table->index(['professional_id', 'status', 'expires_at']);
            $table->index(['job_request_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_invitations');
    }
};
