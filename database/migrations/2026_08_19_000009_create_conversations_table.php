<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_request_id')->nullable()->unique()->constrained('job_requests')->nullOnDelete();
            $table->foreignId('client_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('professional_id')->constrained('professional_profiles')->restrictOnDelete();
            $table->timestamps();

            $table->index(['client_id', 'professional_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
