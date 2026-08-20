<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_disputes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_request_id')->unique()->constrained('job_requests')->restrictOnDelete();
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
            $table->string('reason', 40);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('open')->index();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['opened_by', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_disputes');
    }
};
